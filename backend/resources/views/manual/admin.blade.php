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
    <h1>Administrator User Manual</h1>
    <p class="intro">Digital Trainee Tracker &middot; Agricultural Training Institute, Caraga Region</p>

    <h2>1. Signing In</h2>
    <p>Open the portal and sign in with your administrator username and password.</p>

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
    <p>Your Dashboard shows system-wide totals — trainees, document compliance, attendance trends, and completion rates by province.</p>

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
        <li><b>1</b> The sidebar — Dashboard, User Accounts, Settings, Audit Logs, Storage Monitor, Training Programs, Reports.</li>
    </ol>

    <h2>3. Managing User Accounts</h2>
    <p>Under <b>User Accounts</b>, create, deactivate, or delete login accounts for officers, admins, and trainees, and link a trainee's login to their profile.</p>

    <div class="mock-screen" style="height: 100px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #eee;">Erl John Pocon &middot; project_officer</td>
                <td style="width: 60px; text-align: right;"><span class="mock-pill pill-green">Active</span></td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 410px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Status toggle — deactivate an account without deleting it.</li>
    </ol>

    <h2>4. System Settings</h2>
    <p>Under <b>Settings</b>, configure the current academic/training term, training program categories, organization name, and the list of Required Post-Training Documents (with an Active/Inactive toggle per document).</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">E-Certify &middot; 12345</td>
                <td style="width: 140px; text-align: right;">
                    <span class="mock-button" style="background:#64748b;">Inactive</span>
                    <span class="mock-button">Activate</span>
                </td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 300px;">1</div>
        <div class="badge" style="top: 2px; left: 360px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Current status of this required document.</li>
        <li><b>2</b> Toggle whether trainees are required to submit it.</li>
    </ol>

    <h2>5. Audit Logs</h2>
    <p>Under <b>Audit Logs</b>, review every significant action taken in the system — logins, approvals, record changes — each with who did it and when.</p>

    <h2>6. Storage Monitor</h2>
    <p>Under <b>Storage Monitor</b>, see live disk usage on the server (not sample data) to anticipate when more storage will be needed for uploaded documents.</p>

    <h2>7. Training Programs</h2>
    <p>Admins have the same access as Project Officers to create and manage training programs — name, chain level, venue, dates, and status.</p>

    <h2>8. Viewing, Printing &amp; Downloading Reports</h2>
    <p>Under <b>Reports</b>, each report card has a <b>View / Print</b> button (opens a PDF in a new tab) and a <b>Download CSV</b> button for raw data.</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px;">
            <p style="margin: 0 0 6px 0; font-weight: bold;">Trainee Demographics Report</p>
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
