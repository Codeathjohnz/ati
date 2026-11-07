<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion - {{ $trainee->full_name }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e2419;
            margin: 0;
            padding: 0;
        }
        .frame {
            border: 3px solid #2f5233;
            margin: 10px;
            padding: 0;
        }
        .frame-inner {
            border: 1px solid #a9781f;
            margin: 4px;
            padding: 16px 50px;
            text-align: center;
        }
        .badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #2f5233;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            line-height: 42px;
            margin: 0 auto 4px auto;
        }
        .org-name { font-size: 12px; letter-spacing: 2px; color: #2f5233; font-weight: bold; text-transform: uppercase; }
        .org-sub { font-size: 9px; color: #666; margin-top: 1px; }
        .title {
            font-size: 23px;
            font-weight: bold;
            color: #2f5233;
            margin-top: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .subtitle { font-size: 10.5px; color: #666; margin-top: 2px; }
        .presented-to { font-size: 10px; color: #666; margin-top: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .trainee-name {
            font-size: 21px;
            font-weight: bold;
            color: #1e2419;
            margin-top: 4px;
            padding-bottom: 4px;
            border-bottom: 1px solid #c7c0a8;
            display: inline-block;
            min-width: 420px;
        }
        .body-text { font-size: 10px; color: #333; line-height: 1.4; margin: 10px auto 0 auto; max-width: 580px; }

        .program-box {
            margin: 10px auto 0 auto;
            max-width: 560px;
            border: 1px solid #e1e0d9;
            border-radius: 6px;
            padding: 8px 18px;
            background: #f7f6f1;
        }
        .program-title { font-size: 11.5px; font-weight: bold; color: #2f5233; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .program-list { font-size: 9.5px; color: #333; text-align: left; margin: 0; padding-left: 18px; line-height: 1.35; }

        table.levels { width: 100%; max-width: 560px; margin: 10px auto 0 auto; border-collapse: collapse; }
        table.levels th, table.levels td { border: 1px solid #e1e0d9; padding: 2.5px 10px; font-size: 9px; }
        table.levels th { background: #2f5233; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        .footer-row { margin-top: 14px; width: 100%; }
        .footer-cell { display: inline-block; vertical-align: top; width: 32%; font-size: 9px; color: #555; }
        .sign-line { border-top: 1px solid #333; margin-top: 18px; padding-top: 3px; width: 150px; margin-left: auto; margin-right: auto; }
        .qr-img { width: 58px; height: 58px; }
        .cert-number { font-size: 9px; color: #666; margin-top: 3px; }
    </style>
</head>
<body>
    <div class="frame">
        <div class="frame-inner">
            <div class="badge">AT</div>
            <div class="org-name">Agricultural Training Institute &mdash; Caraga Region</div>
            <div class="org-sub">Digital Trainee Tracker</div>

            <div class="title">Certificate of Completion</div>
            <div class="subtitle">Trainee Profiling, Document Status Tracking &amp; Competency Gap Analysis Program</div>

            <div class="presented-to">This certificate is proudly presented to</div>
            <div class="trainee-name">{{ $trainee->full_name }}</div>

            <p class="body-text">
                for successfully completing all five chain levels of agricultural value-chain
                training &mdash; Production, Post-Harvest, Processing, Marketing, and Consumption &mdash;
                and is hereby recognized as an
            </p>

            <div class="program-box">
                <div class="program-title">Empowered &amp; Bankable Farmer</div>
                <ul class="program-list">
                    <li>Increase Production</li>
                    <li>Increase Adoption of Technologies</li>
                    <li>Reduce Post-Harvest Losses</li>
                    <li>Diversification Opportunities</li>
                    <li>Access to Market</li>
                </ul>
            </div>

            <table class="levels">
                <thead>
                    <tr><th>Chain Level</th><th>Date Completed</th></tr>
                </thead>
                <tbody>
                    @foreach ($certificate->chain_levels ?? [] as $level)
                        <tr>
                            <td>{{ $level['chain_level'] }}</td>
                            <td>{{ $level['date_attended'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="footer-row">
                <div class="footer-cell" style="text-align: left;">
                    <div class="sign-line" style="margin-left: 0;">Project Officer</div>
                </div>
                <div class="footer-cell">
                    <img class="qr-img" src="{{ $qrDataUri }}">
                    <div class="cert-number">Scan to verify</div>
                </div>
                <div class="footer-cell" style="text-align: right;">
                    <div class="sign-line" style="margin-right: 0;">Regional Director</div>
                </div>
            </div>

            <div class="cert-number">
                Certificate No. {{ $certificate->certificate_number }} &middot; Issued {{ $certificate->issued_at->format('F j, Y') }}
            </div>
        </div>
    </div>
</body>
</html>
