<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity History - {{ $trainee->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin-top: 24px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f2f2f2; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .yes { background: #dcfce7; color: #166534; }
        .no { background: #fee2e2; color: #991b1b; }
        .passed { background: #dcfce7; color: #166534; }
        .failed { background: #fee2e2; color: #991b1b; }
        .complete { background: #dcfce7; color: #166534; }
        .gap { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <h1>ATI-Caraga Trainee Activity History</h1>
    <p class="muted">Generated {{ now()->format('F j, Y g:i A') }}</p>

    <h2>Trainee Profile</h2>
    <table>
        <tr><th>Full Name</th><td>{{ $trainee->full_name }}</td><th>Sex</th><td>{{ $trainee->sex }}</td></tr>
        <tr><th>Province</th><td>{{ $trainee->province }}</td><th>City/Municipality</th><td>{{ $trainee->city_municipality }}</td></tr>
        <tr><th>Educational Level</th><td>{{ $trainee->educational_level }}</td><th>Organization</th><td>{{ $trainee->organization_affiliation }}</td></tr>
    </table>

    <h2>Training History</h2>
    <table>
        <thead>
            <tr><th>Training Program</th><th>Chain Level</th><th>Date Attended</th></tr>
        </thead>
        <tbody>
            @forelse ($trainee->trainingHistory as $history)
                <tr>
                    <td>{{ $history->trainingProgram?->name }}</td>
                    <td>{{ $history->chain_level }}</td>
                    <td>{{ optional($history->date_attended)->toDateString() }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No training history on record.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Document Compliance (Overall: <span class="badge {{ strtolower($documentCompliance['overall_status']) === 'passed' ? 'passed' : 'failed' }}">{{ $documentCompliance['overall_status'] }}</span>)</h2>
    @forelse ($documentCompliance['per_training'] as $entry)
        <table>
            <thead>
                <tr><th colspan="2">{{ $entry['training_program_name'] }} &mdash; {{ $entry['overall_status'] }}</th></tr>
                <tr><th>Document</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach ($entry['documents'] as $doc)
                    <tr>
                        <td>{{ $doc['name'] }}</td>
                        <td><span class="badge {{ $doc['submitted'] ? 'yes' : 'no' }}">{{ $doc['submitted'] ? 'Yes' : 'No' }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="muted">No training records to evaluate document compliance against.</p>
    @endforelse

    <h2>Competency Gap Analysis</h2>
    <table>
        <thead>
            <tr><th>Chain Level</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($competencyGaps['chain_levels'] as $level)
                <tr>
                    <td>{{ $level['chain_level'] }}</td>
                    <td><span class="badge {{ $level['completed'] ? 'complete' : 'gap' }}">{{ $level['status'] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
