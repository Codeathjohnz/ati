<?php

namespace Database\Seeders;

use App\Models\RequiredDocument;
use Illuminate\Database\Seeder;

/**
 * Seeds the six required post-training document types named in the
 * capstone document's "Operational Definition of Terms" (Document Status
 * Tracking). This is controlled vocabulary, not fabricated trainee data.
 */
class RequiredDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            ['code' => 're_entry_plan', 'name' => 'Re-Entry Plan', 'sort_order' => 1],
            ['code' => 'pre_test', 'name' => 'Pre-Test', 'sort_order' => 2],
            ['code' => 'post_test', 'name' => 'Post-Test', 'sort_order' => 3],
            ['code' => 'evaluation_form', 'name' => 'Evaluation Form', 'sort_order' => 4],
            ['code' => 'attendance_sheet', 'name' => 'Attendance Form', 'sort_order' => 5],
            ['code' => 'information_sheet', 'name' => 'Information Form', 'sort_order' => 6],
        ];

        foreach ($documents as $document) {
            RequiredDocument::updateOrCreate(
                ['code' => $document['code']],
                [...$document, 'is_active' => true]
            );
        }
    }
}
