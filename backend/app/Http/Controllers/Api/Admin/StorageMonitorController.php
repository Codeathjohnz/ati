<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

/**
 * Real, live filesystem usage for the server this backend container runs
 * on — no hardcoded or sample figures. Meant to be cross-checked against
 * `df -h` run in the same container (Dokploy's terminal for the `backend`
 * service): both read the same mounted filesystem, so the numbers should
 * match within rounding.
 */
class StorageMonitorController extends Controller
{
    /** Top-level app directories worth reporting individually — everything else folds into "Other". */
    private const TRACKED_PATHS = [
        'vendor' => 'vendor',
        'storage' => 'storage',
        'bootstrap/cache' => 'bootstrap/cache',
        'app' => 'app',
        'public' => 'public',
        'database' => 'database',
        'resources' => 'resources',
        'routes' => 'routes',
        'config' => 'config',
        'tests' => 'tests',
    ];

    public function index()
    {
        $root = base_path();

        $total = disk_total_space($root);
        $free = disk_free_space($root);
        $used = $total !== false && $free !== false ? $total - $free : null;

        $consumers = collect(self::TRACKED_PATHS)
            ->map(function (string $relative, string $label) use ($root) {
                $path = $root.DIRECTORY_SEPARATOR.$relative;

                return [
                    'label' => $label,
                    'path' => $relative,
                    'size_bytes' => is_dir($path) ? $this->directorySize($path) : 0,
                ];
            })
            ->sortByDesc('size_bytes')
            ->values();

        $trackedTotal = $consumers->sum('size_bytes');
        $appTotal = $this->directorySize($root);

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'filesystem' => [
                'path' => $root,
                'total_bytes' => $total,
                'used_bytes' => $used,
                'free_bytes' => $free,
                'used_percent' => $total ? round(($used / $total) * 100, 1) : null,
            ],
            'app_total_bytes' => $appTotal,
            'top_consumers' => $consumers->map(fn ($c) => [
                ...$c,
                'percent_of_app' => $appTotal > 0 ? round(($c['size_bytes'] / $appTotal) * 100, 1) : 0,
            ]),
            'other_bytes' => max($appTotal - $trackedTotal, 0),
        ]);
    }

    /**
     * Byte sum for a directory. Prefers shelling out to the native `du -sb`
     * (fast, C-implemented directory-tree traversal — exactly what `df`/`du`
     * on the server itself would compute, which is the whole point of this
     * page: it's meant to be cross-checked against `du`/`df` run directly in
     * the same container). Falls back to a pure-PHP recursive scan only
     * when `du` isn't available (e.g. local Windows dev) — capped so a huge
     * tree can't hang the request indefinitely.
     */
    private function directorySize(string $path): int
    {
        if (PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec')) {
            $output = @shell_exec('du -sb '.escapeshellarg($path).' 2>/dev/null');
            if ($output && preg_match('/^(\d+)/', trim($output), $matches)) {
                return (int) $matches[1];
            }
        }

        return $this->directorySizeViaIterator($path);
    }

    private function directorySizeViaIterator(string $path): int
    {
        $total = 0;
        $filesCounted = 0;
        $cap = 20000; // local-dev fallback only — keeps a huge tree (e.g. vendor/) from hanging the request

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $total += $file->getSize();
                    if (++$filesCounted >= $cap) {
                        break;
                    }
                }
            }
        } catch (UnexpectedValueException) {
            // Directory vanished or became unreadable mid-scan — report what was counted so far.
        }

        return $total;
    }
}
