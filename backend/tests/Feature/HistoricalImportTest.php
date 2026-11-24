<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class HistoricalImportTest extends TestCase
{
    use RefreshDatabase;

    private function writeXlsx(array $header, array $rows, string $path): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        (new Xlsx($spreadsheet))->save($path);
    }

    public function test_importing_tmis_creates_historical_records_invisible_to_live_queries(): void
    {
        $path = sys_get_temp_dir().'/tmis_fixture_'.uniqid().'.xlsx';

        $header = [
            'Date Submitted', 'tacr_createdby', 'Type of Training', 'Fund Source', 'Title of Activity',
            'Date Started', 'Date Ended', 'Venue', 'Fullname', 'Last Name', 'First Name', 'Gender',
            'Civil Status', 'Date of Birth', 'Educational Attainment', 'Degree', 'Home (Region)',
            'Home (Province)', 'Home (City/Municipality)', 'Home (Barangay)', 'Type of Participants',
            'Position/Designation', 'Contact Number', 'Email Address', 'Office Address',
        ];

        $rows = [
            [
                '23/03/2021', 'jane-ATI-13', 'Other Training', 'Regular', 'Urban Vegetable Gardening',
                '03/10/2021', '03/12/2021', 'Barangay Bit-os', 'Jaymar Marabe', 'Marabe', 'Jaymar', 'MALE',
                'SINGLE', '27/11/1997', 'College Level', 'None', 'Region 13 CARAGA', 'Agusan Del Norte',
                'Butuan City', 'Bit-os', 'FARMER', 'Farmer', '9505477566', 'N/A', 'Bit-os',
            ],
            [
                '23/03/2021', 'jane-ATI-13', 'Other Training', 'Regular', 'Urban Vegetable Gardening',
                '03/10/2021', '03/12/2021', 'Barangay Bit-os', 'Josie Adecer', 'Adecer', 'Josie', 'FEMALE',
                'MARRIED', '05/11/1962', 'Highschool Graduate', 'N/A', 'Region 13 CARAGA', 'Agusan Del Norte',
                'Butuan City', 'Bit-os', 'FARMER', 'Farmer', '93097463228', 'N/A', 'Bit-os',
            ],
            // Same person + same training repeated — must not create a duplicate attendance row.
            [
                '24/03/2021', 'jane-ATI-13', 'Other Training', 'Regular', 'Urban Vegetable Gardening',
                '03/10/2021', '03/12/2021', 'Barangay Bit-os', 'Jaymar Marabe', 'Marabe', 'Jaymar', 'MALE',
                'SINGLE', '27/11/1997', 'College Level', 'None', 'Region 13 CARAGA', 'Agusan Del Norte',
                'Butuan City', 'Bit-os', 'FARMER', 'Farmer', '9505477566', 'N/A', 'Bit-os',
            ],
        ];

        $this->writeXlsx($header, $rows, $path);

        $this->artisan('import:tmis', ['path' => $path])->assertSuccessful();

        unlink($path);

        // Created as historical, so the normal (live) query sees none of it.
        $this->assertSame(0, TraineeProfile::count());
        $this->assertSame(0, TrainingProgram::count());
        $this->assertSame(0, TrainingHistory::count());

        // But the data really is there once explicitly asked for.
        $this->assertSame(2, TraineeProfile::onlyHistorical()->count());
        $this->assertSame(1, TrainingProgram::onlyHistorical()->count());
        $this->assertSame(2, TrainingHistory::onlyHistorical()->count()); // not 3 — the repeat was deduped

        $jaymar = TraineeProfile::onlyHistorical()->where('first_name', 'Jaymar')->first();
        $this->assertNotNull($jaymar);
        $this->assertSame('1997-11-27', $jaymar->birthdate->toDateString());
        $this->assertSame('Male', $jaymar->sex);
        $this->assertNull($jaymar->user_id);
    }

    public function test_importing_rp_evaluations_fills_both_historical_tables(): void
    {
        $dir = sys_get_temp_dir().'/rp_fixture_'.uniqid();
        mkdir($dir);

        $this->writeXlsx(
            ['Respondent Num', 'Respondent Name', 'Rp Code', 'Training Code', 'Resource Person Name', 'Training Title', 'Delivery Date', 'Clarity Objectives', 'Topic Organization', 'Clarity Presentation', 'Instructional Aids Quality', 'Teaching Ability', 'Question Answering Ability', 'Participant Interest', 'Time Management', 'Topic Ending', 'Overall Satisfaction', 'Liked About Rp', 'Disliked About Rp', 'Other Remarks'],
            [[1, 'Gina P. Perudez', '#RP-E2CIU', 'ATI-13-cdms-ZNQS0', 'Mr. Anecito M. Anuada', 'Refresher Course', '2026-02-20', 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 'Okay kaayo', 'N/A', 'N/A']],
            $dir.'/RP_Full_Evaluation_Report_2026.xlsx'
        );

        $this->writeXlsx(
            ['Respondent Num', 'Respondent Name', 'Rp Code', 'Training Code', 'Resource Person Name', 'Training Title', 'Delivery Date', 'Topics Discussed', 'Relevance', 'Time Allocation', 'Suggested Time Allocation', 'Methodology Used', 'Suggested Methodology'],
            [[1, 'Edgen A. Maturan', '#RP-S4YZD', 'ATI-13-CDMSS-4DOTF', 'Mary Grace B. Salve', 'Basic Agri Extension', '2026-02-20', 'Lesson 5', 3, 3, 'None', 3, 'None']],
            $dir.'/RP_Topic_Evaluation_Report_2026.xlsx'
        );

        $this->artisan('import:rp-evaluations', ['dir' => $dir])->assertSuccessful();

        $this->assertDatabaseHas('historical_rp_evaluations', [
            'respondent_name' => 'Gina P. Perudez',
            'rp_code' => '#RP-E2CIU',
            'overall_satisfaction' => 5,
            'disliked_about_rp' => null, // "N/A" normalized away
        ]);

        $this->assertDatabaseHas('historical_rp_topic_evaluations', [
            'respondent_name' => 'Edgen A. Maturan',
            'topic' => 'Lesson 5',
            'relevance' => 3,
            'suggested_time_allocation' => null, // "None" normalized away
        ]);

        array_map('unlink', glob($dir.'/*.xlsx'));
        rmdir($dir);
    }
}
