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
    <h1>Trainee User Manual</h1>
    <p class="intro">Digital Trainee Tracker &middot; Agricultural Training Institute, Caraga Region</p>

    <h2>1. Signing In</h2>
    <p>Open the portal in your browser and sign in with the username and password given to you by your Project Officer. If you have a Google account using the same email on file, you can also use the "Continue with Google" button for a faster sign-in.</p>

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

    <h2>2. Your Dashboard</h2>
    <p>After signing in you land on your Dashboard, showing your training count, document compliance, and competency gap progress at a glance.</p>

    <div class="mock-screen" style="height: 170px;">
        <table class="mock-table"><tr>
            <td class="mock-sidebar">
                <div class="mock-logo"></div>
                <div class="mock-nav active"></div>
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
        <div class="badge" style="top: 45px; left: 100px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> The sidebar menu — navigate between Dashboard, Training History, My Documents, Upcoming Training, and Activity History.</li>
        <li><b>2</b> Your stat tiles — trainings attended, document compliance, and competency gaps.</li>
    </ol>

    <h2>3. Checking Document Status &amp; Uploading</h2>
    <p>Go to <b>My Documents</b> to see every required document per training you attended, and whether it's been submitted, approved, or needs revision. Click Upload to attach a file for anything still outstanding.</p>

    <div class="mock-screen" style="height: 120px;">
        <div style="position: absolute; top: 10px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #eee;">Re-Entry Plan</td>
                <td style="width: 90px; text-align: right;"><span class="mock-pill pill-amber">Needs Revision</span></td>
            </tr><tr>
                <td style="padding: 6px 0; border-bottom: 1px solid #eee;">Pre-Test</td>
                <td style="width: 90px; text-align: right;"><span class="mock-button" style="width:50px;text-align:center;">Upload</span></td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 430px;">1</div>
        <div class="badge" style="top: 30px; left: 350px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Status badge for each document — Not Submitted, Pending Review, Approved, or Needs Revision.</li>
        <li><b>2</b> The Upload button (or "Replace upload" once you've already submitted one).</li>
    </ol>

    <h2>4. Confirming (or Cancelling) Upcoming Training</h2>
    <p>Under <b>Upcoming Training</b>, confirm your participation for each scheduled activity. If an emergency comes up after you've already confirmed, you can still cancel it from the same screen.</p>

    <div class="mock-screen" style="height: 100px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">Rice Guarding Training &middot; Post-Harvest</td>
                <td style="width: 160px; text-align: right;">
                    <span class="mock-pill pill-green">confirmed</span>
                    <span class="mock-button" style="width:70px;text-align:center;">Cancel</span>
                </td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 300px;">1</div>
        <div class="badge" style="top: 2px; left: 375px;">2</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Your current response status.</li>
        <li><b>2</b> "Cancel (emergency)" — changes a confirmed response back to declined.</li>
    </ol>

    <h2>5. Registering for a Training</h2>
    <p>Under <b>Available Trainings</b>, browse every training your Project Officer has opened for registration and click Register if you're interested. You can withdraw later if your plans change.</p>

    <div class="mock-screen" style="height: 90px;">
        <div style="position: absolute; top: 14px; left: 14px; width: 420px;">
            <table class="mock-table"><tr>
                <td style="padding: 6px 0;">Basic Agricultural Extension Course</td>
                <td style="width: 90px; text-align: right;"><span class="mock-button">Register</span></td>
            </tr></table>
        </div>
        <div class="badge" style="top: 2px; left: 400px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Register your interest — your Project Officer sees how many signed up.</li>
    </ol>

    <h2>6. Scanning the Attendance QR Code</h2>
    <p>On training day, your Project Officer displays a QR code. Scan it with your phone's own camera — no app needed — and it opens a page that records your attendance automatically once you're signed in.</p>

    <h2>7. Submitting the Resource Person Evaluation</h2>
    <p>After attending, click <b>Evaluate</b> next to that training on your Training History page to rate the topics covered and the resource person who delivered them. This is one of the steps needed before that training's certificate becomes available.</p>

    <h2>8. Downloading a Training Certificate</h2>
    <p>Certificates are earned per training, not all at once. Once you've registered, attended, passed its pre-test and post-test (if the officer set any), and submitted its Resource Person evaluation, a Certificate button appears next to that training on your Training History page. The certificate includes a QR code anyone can scan to verify it's genuine.</p>

    <div class="mock-screen" style="height: 90px; background: #f0fdf4;">
        <div style="position: absolute; top: 14px; left: 14px;">
            <p style="margin:0; font-weight:bold; color:#166534;">Hands-on Training on Abaca Technologies</p>
            <p style="margin:2px 0 0 0; color:#166534; font-size:10px;">Registered, attended, tested, evaluated.</p>
        </div>
        <div class="mock-button" style="position:absolute; top: 30px; left: 300px;">🎓 Certificate</div>
        <div class="badge" style="top: 14px; left: 400px;">1</div>
    </div>
    <ol class="legend">
        <li><b>1</b> Click to download that training's certificate as a PDF, with a QR code for verification.</li>
    </ol>

    <h2>9. Resetting a Forgotten Password</h2>
    <p>On the sign-in page, click <b>Forgot password?</b>, enter the email on your account, and a reset link will be sent to it. Open the email, click the link, and choose a new password.</p>

    <h2>10. Using the Help Bubble</h2>
    <p>A small <b>?</b> button stays in the bottom-right corner of every screen. Click it any time for quick tips about the page you're currently on, or to jump to this full manual.</p>
</div>
