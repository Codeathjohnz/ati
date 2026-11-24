<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports the historical RP (Resource Person) evaluation report exports —
 * "Full Evaluation" files (one row per respondent, 10 Likert scores) and
 * "Topic Evaluation" files (one row per respondent per topic) — into their
 * own historical tables (see the 2026_11_25_000007 migration for why they
 * aren't forced into the live rp_evaluations table). Bulk-inserted in
 * chunks rather than one Eloquent::create() per row, since these are
 * ~20,000 rows of plain historical log data with no relations to resolve.
 */
class ImportRpEvaluationsDataset extends Command
{
    protected $signature = 'import:rp-evaluations {dir : Directory containing the RP evaluation xlsx files}';

    protected $description = 'Import the historical RP evaluation report exports into the historical_rp_evaluations tables';

    private const CHUNK_SIZE = 500;

    public function handle(): int
    {
        $dir = rtrim($this->argument('dir'), '/\\');
        if (! is_dir($dir)) {
            $this->error("Directory not found: {$dir}");

            return self::FAILURE;
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $fullTotal = 0;
        $topicTotal = 0;

        foreach (glob($dir.'/*.xlsx') as $file) {
            $basename = basename($file);

            if (stripos($basename, 'Full_Evaluation') !== false) {
                $count = $this->importFull($file, $basename);
                $fullTotal += $count;
                $this->info("  {$basename}: {$count} full-evaluation rows imported.");
            } elseif (stripos($basename, 'Topic_Evaluation') !== false) {
                $count = $this->importTopic($file, $basename);
                $topicTotal += $count;
                $this->info("  {$basename}: {$count} topic-evaluation rows imported.");
            } else {
                $this->comment("  Skipping unrecognized file: {$basename}");
            }
        }

        $this->newLine();
        $this->table(
            ['Table', 'Rows imported'],
            [
                ['historical_rp_evaluations', $fullTotal],
                ['historical_rp_topic_evaluations', $topicTotal],
            ]
        );

        return self::SUCCESS;
    }

    private function importFull(string $file, string $basename): int
    {
        $rows = $this->readRows($file);
        $count = 0;
        $buffer = [];

        foreach ($rows as $row) {
            $buffer[] = [
                'respondent_num' => $this->intOrNull($row['Respondent Num'] ?? null),
                'respondent_name' => $this->strOrNull($row['Respondent Name'] ?? null),
                'rp_code' => $this->strOrNull($row['Rp Code'] ?? null),
                'training_code' => $this->strOrNull($row['Training Code'] ?? null),
                'resource_person_name' => $this->strOrNull($row['Resource Person Name'] ?? null),
                'training_title' => $this->strOrNull($row['Training Title'] ?? null),
                'delivery_date' => $this->parseDate($row['Delivery Date'] ?? null),
                'clarity_objectives' => $this->intOrNull($row['Clarity Objectives'] ?? null),
                'topic_organization' => $this->intOrNull($row['Topic Organization'] ?? null),
                'clarity_presentation' => $this->intOrNull($row['Clarity Presentation'] ?? null),
                'instructional_aids_quality' => $this->intOrNull($row['Instructional Aids Quality'] ?? null),
                'teaching_ability' => $this->intOrNull($row['Teaching Ability'] ?? null),
                'question_answering_ability' => $this->intOrNull($row['Question Answering Ability'] ?? null),
                'participant_interest' => $this->intOrNull($row['Participant Interest'] ?? null),
                'time_management' => $this->intOrNull($row['Time Management'] ?? null),
                'topic_ending' => $this->intOrNull($row['Topic Ending'] ?? null),
                'overall_satisfaction' => $this->intOrNull($row['Overall Satisfaction'] ?? null),
                'liked_about_rp' => $this->strOrNull($row['Liked About Rp'] ?? null),
                'disliked_about_rp' => $this->strOrNull($row['Disliked About Rp'] ?? null),
                'other_remarks' => $this->strOrNull($row['Other Remarks'] ?? null),
                'source_file' => $basename,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($buffer) >= self::CHUNK_SIZE) {
                DB::table('historical_rp_evaluations')->insert($buffer);
                $count += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('historical_rp_evaluations')->insert($buffer);
            $count += count($buffer);
        }

        return $count;
    }

    private function importTopic(string $file, string $basename): int
    {
        $rows = $this->readRows($file);
        $count = 0;
        $buffer = [];

        foreach ($rows as $row) {
            $buffer[] = [
                'respondent_num' => $this->intOrNull($row['Respondent Num'] ?? null),
                'respondent_name' => $this->strOrNull($row['Respondent Name'] ?? null),
                'rp_code' => $this->strOrNull($row['Rp Code'] ?? null),
                'training_code' => $this->strOrNull($row['Training Code'] ?? null),
                'resource_person_name' => $this->strOrNull($row['Resource Person Name'] ?? null),
                'training_title' => $this->strOrNull($row['Training Title'] ?? null),
                'delivery_date' => $this->parseDate($row['Delivery Date'] ?? null),
                'topic' => $this->strOrNull($row['Topics Discussed'] ?? null),
                'relevance' => $this->intOrNull($row['Relevance'] ?? null),
                'time_allocation' => $this->intOrNull($row['Time Allocation'] ?? null),
                'suggested_time_allocation' => $this->strOrNull($row['Suggested Time Allocation'] ?? null),
                'methodology' => $this->intOrNull($row['Methodology Used'] ?? null),
                'suggested_methodology' => $this->strOrNull($row['Suggested Methodology'] ?? null),
                'source_file' => $basename,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($buffer) >= self::CHUNK_SIZE) {
                DB::table('historical_rp_topic_evaluations')->insert($buffer);
                $count += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('historical_rp_topic_evaluations')->insert($buffer);
            $count += count($buffer);
        }

        return $count;
    }

    /** @return array<int, array<string, mixed>> keyed by this file's own header row */
    private function readRows(string $file): array
    {
        $spreadsheet = IOFactory::load($file);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);
        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows) ?? []);

        return array_map(fn ($row) => array_combine($header, array_pad($row, count($header), null)), $rows);
    }

    private const BLANK_PLACEHOLDERS = ['n/a', 'not indicated', 'none'];

    private function strOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || in_array(strtolower($value), self::BLANK_PLACEHOLDERS, true)) {
            return null;
        }

        return $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) round((float) $value) : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
