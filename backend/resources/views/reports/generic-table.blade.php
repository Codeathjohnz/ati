<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="muted">ATI-Caraga Digital Trainee Tracker &middot; Generated {{ now()->format('F j, Y g:i A') }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $head)
                    <th>{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}" class="muted">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
