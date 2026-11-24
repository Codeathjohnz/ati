<style>
    .manual { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #1a1a1a; }
    .manual h1 { font-size: 19px; color: #15803d; margin: 0 0 2px 0; }
    .manual .intro { color: #666; margin: 0 0 18px 0; }
    .manual p { margin: 0 0 8px 0; }
    .manual ol, .manual ul { margin: 0 0 8px 0; }
    .manual h2 { font-size: 13.5px; color: #14532d; border-bottom: 1px solid #d8e5da; padding-bottom: 4px; margin: 20px 0 6px 0; }
    .manual .steps { margin: 0 0 8px 0; padding-left: 18px; }
    .manual .steps li { margin-bottom: 3px; }

    .mock-screen { position: relative; border: 1px solid #cfd8d2; border-radius: 6px; margin: 8px 0 4px 0; background: #f7f9f7; }
    .mock-table { width: 100%; border-collapse: collapse; }
    .mock-sidebar { width: 70px; background: #ffffff; border-right: 1px solid #e5e5e5; padding: 8px 6px; vertical-align: top; }
    .mock-logo { width: 20px; height: 20px; border-radius: 5px; background: #16a34a; margin-bottom: 8px; }
    .mock-nav { height: 7px; border-radius: 3px; background: #e5e7eb; margin-bottom: 5px; }
    .mock-nav.active { background: #bbf7d0; }
    .mock-main { vertical-align: top; padding: 0; }
    .mock-header { height: 20px; border-bottom: 1px solid #e5e5e5; background: #ffffff; }
    .mock-body { padding: 8px; }
    .mock-cards-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; }
    .mock-card { border: 1px solid #e5e7eb; border-radius: 5px; background: #ffffff; height: 42px; }
    .mock-field { border: 1px solid #d8d8d8; border-radius: 4px; background: #ffffff; height: 16px; margin-bottom: 6px; }
    .mock-button { display: inline-block; background: #16a34a; color: #fff; border-radius: 4px; padding: 3px 10px; font-size: 9px; }
    .mock-pill { display: inline-block; border-radius: 10px; padding: 2px 8px; font-size: 8.5px; font-weight: bold; }
    .pill-green { background: #dcfce7; color: #166534; }
    .pill-amber { background: #fef3c7; color: #92400e; }
    .pill-red { background: #fee2e2; color: #991b1b; }

    .badge { position: absolute; width: 16px; height: 16px; border-radius: 50%; background: #dc2626; color: #ffffff; font-size: 9.5px; font-weight: bold; text-align: center; line-height: 16px; }
    .legend { font-size: 10px; color: #333; margin: 4px 0 2px 0; padding-left: 16px; }
    .legend li { margin-bottom: 2px; }
    .legend b { color: #dc2626; }
</style>

<div class="manual">
    <h1>Project Officer User Manual</h1>
    <p class="intro">Digital Trainee Tracker &middot; Agricultural Training Institute, Caraga Region</p>

    <h2>1. Signing In</h2>
    <p>Open the portal and sign in with the username and password given to you by your Administrator.</p>

    <div class="mock-screen" style="height: 110px;">
        <div style="position: absolute; top: 20px; left: 40px; width: 200px;">
            <div class="mock-field"></div>
            <div class="mock-field"></div>
            <div class="mock-button" style="width: 54px; text-align: center;">Sign In</div>
        </div>
        <div class="badge" style="top: 20px; left: 260px;">1</div>
        <div class="badge" style="top: 44px; left: 260px;">2</div>
        <div class="badge" style="top: 68px; left: 260px;">3</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Enter your username or email.</li>
        <li><b>2</b> Enter your password.</li>
        <li><b>3</b> Click Sign In.</li>
    </ol>

    <h2>2. Dashboard Overview</h2>
    <p>Your Dashboard summarizes trainee counts, document compliance, attendance trends, and completion rates across provinces.</p>

    <div class="mock-screen" style="height: 170px;">
        <table class="mock-table"><tr>
            <td class="mock-sidebar">
                <div class="mock-logo"></div>
                <div class="mock-nav active"></div>
                <div class="mock-nav"></div>
                <div class="mock-nav"></div>
                <div class="mock-nav"></div>
                <div class="mock-nav"></div>
                <div class="mock-nav"></div>
            </td>
            <td class="mock-main">
                <div class="mock-header"></div>
                <div class="mock-body">
                    <table class="mock-cards-table"><tr>
                        <td class="mock-card"></td>
                        <td class="mock-card"></td>
                        <td class="mock-card"></td>
                    </tr></table>
                </div>
            </td>
        </tr></table>
        <div class="badge" style="top: 40px; left: 35px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> The sidebar — Dashboard, Trainees, Training Programs, Competency Gaps, Upcoming Confirmations, and Reports.</li>
    </ol>

    <h2>3. Managing Trainees</h2>
    <p>Under <b>Trainees</b>, click <b>Add Trainee</b> to register a new trainee profile, or click an existing row to view/edit their full profile, training history, document compliance, and competency gaps.</p>

    <div class="mock-screen" style="height: 110px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td></td>
                <td style="width: 90px; text-align: right;"><span class="mock-button">+ Add Trainee</span></td>
            </tr></table>
            <table class="mock-table" style="margin-top: 10px;">
                <tr><td style="padding: 6px 0; border-bottom: 1px solid #eee;">Dether John Jubahib Olaguir</td></tr>
                <tr><td style="padding: 6px 0;">Cherrybebs Idul Victoria</td></tr>
            </table>
        </div>
        <div class="badge" style="top: 0px; left: 410px;">1</div>
        <div class="badge" style="top: 36px; left: 16px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Register a new trainee.</li>
        <li><b>2</b> Click any row to open that trainee's full profile.</li>
    </ol>

    <h2>4. Recording Training Attendance</h2>
    <p>On a trainee's profile, use <b>Training History</b> to log each training program they attended, with the chain level and date — this is what drives their document compliance and competency gap tracking.</p>

    <div class="mock-screen" style="height: 100px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">Rice Guarding Training &middot; Post-Harvest &middot; 2026-09-19</td>
            </tr></table>
        </div>
        <div class="mock-button" style="position: absolute; top: 14px; left: 360px;">+ Add</div>
        <div class="badge" style="top: 2px; left: 350px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Add a training attendance record for this trainee.</li>
    </ol>

    <h2>5. Reviewing &amp; Approving Submitted Documents</h2>
    <p>Under a trainee's Document Compliance tab, review each uploaded file and either Approve it or send it back with a remark for revision.</p>

    <div class="mock-screen" style="height: 100px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">Re-Entry Plan.pdf</td>
                <td style="width: 160px; text-align: right;">
                    <span class="mock-button">Approve</span>
                    <span class="mock-button" style="background:#dc2626;">Send Back</span>
                </td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 300px;">1</div>
        <div class="badge" style="top: 2px; left: 375px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Approve — counts toward the trainee's compliance.</li>
        <li><b>2</b> Send Back — add a remark explaining what's missing.</li>
    </ol>

    <h2>6. Managing Training Programs</h2>
    <p>Under <b>Training Programs</b>, create and edit programs: name, one or more chain levels it covers, resource person and topics (for the evaluation form), venue, dates, and status. Check "Open for trainee registration" to let trainees sign up, and add pre/post-tests from the Tests button.</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <div class="mock-field"></div>
            <table class="mock-table"><tr><td style="width: 50%;"><div class="mock-field"></div></td><td style="width: 50%; padding-left: 8px;"><div class="mock-field"></div></td></tr></table>
            <div class="mock-button" style="width: 50px; text-align: center;">Save</div>
        </div>
        <div class="badge" style="top: 2px; left: 440px;">1</div>
        <div class="badge" style="top: 50px; left: 440px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Program name.</li>
        <li><b>2</b> Chain level(s) and venue/dates.</li>
    </ol>

    <h2>7. Registrants &amp; QR Attendance</h2>
    <p>Each posted program shows a registrant count on the Training Programs list — click it to see who signed up. Click <b>Attendance QR</b> to generate a scannable code for check-in day; trainees scan it with their own phone camera to record their attendance automatically, no extra app needed.</p>

    <h2>8. Competency Gaps &amp; Issuing Certificates</h2>
    <p>The <b>Competency Gaps</b> tab on a trainee's profile still shows chain-level progress, but certificates are no longer gated by it. A certificate becomes available per training, once a trainee has registered, attended, passed its tests (if any were published), and submitted its evaluation — click the Certificate button next to that training on their Training History tab.</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">Rice Guarding Training &middot; Post-Harvest</td>
                <td style="width: 90px; text-align: right;"><span class="mock-button">🎓 Certificate</span></td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 400px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Download that training's certificate as a PDF (includes a QR code for verification) — only works once every step is complete.</li>
    </ol>

    <h2>9. Confirming Upcoming Training Participants</h2>
    <p>Under <b>Upcoming Confirmations</b>, see who has confirmed, declined, or not yet responded for each scheduled training.</p>

    <h2>10. Viewing, Printing &amp; Downloading Reports</h2>
    <p>Under <b>Reports</b>, each report card has a <b>View / Print</b> button (opens a PDF in a new tab — use your browser's own print/download icons) and a <b>Download CSV</b> button for raw data.</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px;">
            <p style="margin: 0 0 6px 0; font-weight: bold;">Training Attendance Report</p>
            <span class="mock-button">View / Print</span>
            <span class="mock-button" style="background:#64748b;">Download CSV</span>
        </div>
        <div class="badge" style="top: 0px; left: 110px;">1</div>
        <div class="badge" style="top: 0px; left: 220px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Opens a print-ready PDF in a new tab.</li>
        <li><b>2</b> Downloads the raw data as a spreadsheet-friendly CSV file.</li>
    </ol>
</div>
