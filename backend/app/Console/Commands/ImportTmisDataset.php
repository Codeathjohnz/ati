<?php

namespace App\Console\Commands;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports the historical TMIS 2021-2024 training register as historical
 * trainee/program/attendance records — never a login account, and never
 * visible to the live portal (see ExcludesHistoricalByDefault). TMIS has no
 * chain-level column, so imported programs get chain_levels = [] rather
 * than a guessed mapping; they don't contribute to competency gap analysis,
 * which is scoped to live portal activity.
 */
class ImportTmisDataset extends Command
{
    protected $signature = 'import:tmis {path : Path to the TMIS xlsx file}';

    protected $description = 'Import the historical TMIS training register as historical records (no login accounts created)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $this->info('Reading spreadsheet — this can take a moment for a large file…');
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);
        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows));
        $columnIndex = array_flip($header);

        $totalRows = count($rows);
        $attendanceCreated = 0;
        $traineesCreated = 0;
        $programsCreated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $row) {
            $bar->advance();

            $get = fn (string $col) => isset($columnIndex[$col]) ? trim((string) ($row[$columnIndex[$col]] ?? '')) : '';

            $firstName = $get('First Name');
            $lastName = $get('Last Name');
            $title = $get('Title of Activity');

            if ($firstName === '' || $lastName === '' || $title === '') {
                $skipped++;

                continue;
            }

            $province = $get('Home (Province)') ?: 'Unknown';
            $startDate = $this->parseDate($get('Date Started')) ?? now()->toDateString();

            $trainee = TraineeProfile::onlyHistorical()
                ->whereRaw('LOWER(first_name) = ?', [mb_strtolower($firstName)])
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($lastName)])
                ->where('province', $province)
                ->first();

            if (! $trainee) {
                $trainee = TraineeProfile::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'sex' => strtoupper($get('Gender')) === 'FEMALE' ? 'Female' : 'Male',
                    'birthdate' => $this->parseDate($get('Date of Birth')),
                    'contact_number' => $get('Contact Number') ?: null,
                    'barangay' => $get('Home (Barangay)') ?: null,
                    'city_municipality' => $get('Home (City/Municipality)') ?: 'Unknown',
                    'province' => $province,
                    'region' => $get('Home (Region)') ?: 'Caraga',
                    'educational_level' => $this->mapEducation($get('Educational Attainment')),
                    'occupation' => $get('Type of Participants') ?: null,
                    'is_historical' => true,
                ]);
                $traineesCreated++;
            }

            $program = TrainingProgram::onlyHistorical()
                ->where('name', $title)
                ->whereDate('start_date', $startDate)
                ->first();

            if (! $program) {
                $program = TrainingProgram::create([
                    'name' => $title,
                    'description' => $get('Type of Training') ?: null,
                    'chain_levels' => [],
                    'venue' => $get('Venue') ?: null,
                    'start_date' => $startDate,
                    'end_date' => $this->parseDate($get('Date Ended')),
                    'status' => 'completed',
                    'is_posted' => false,
                    'is_historical' => true,
                ]);
                $programsCreated++;
            }

            $exists = TrainingHistory::onlyHistorical()
                ->where('trainee_id', $trainee->id)
                ->where('training_program_id', $program->id)
                ->exists();

            if (! $exists) {
                TrainingHistory::create([
                    'trainee_id' => $trainee->id,
                    'training_program_id' => $program->id,
                    'chain_levels' => [],
                    'date_attended' => $startDate,
                    'is_historical' => true,
                ]);
                $attendanceCreated++;
            } else {
                $skipped++;
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(
            ['Metric', 'Count'],
            [
                ['Rows in file', $totalRows],
                ['Historical trainees created', $traineesCreated],
                ['Historical programs created', $programsCreated],
                ['Attendance records created', $attendanceCreated],
                ['Skipped (missing name/title, or duplicate)', $skipped],
            ]
        );

        return self::SUCCESS;
    }

    private function parseDate(?string $value): ?string
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

        // The source export writes dates as DD/MM/YYYY — Carbon::parse()'s
        // default American MM/DD/YYYY assumption would silently swap day
        // and month for most rows, so that format is tried explicitly first.
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            [, $day, $month, $year] = $m;
            if ((int) $month <= 12 && (int) $day <= 31) {
                try {
                    return Carbon::createFromDate((int) $year, (int) $month, (int) $day)->toDateString();
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapEducation(string $value): ?string
    {
        $value = strtolower($value);

        return match (true) {
            str_contains($value, 'post grad') => 'Post Graduate',
            str_contains($value, 'college') => 'College',
            str_contains($value, 'vocational') => 'Vocational',
            str_contains($value, 'high school') || str_contains($value, 'highschool') => 'High School',
            str_contains($value, 'elementary') => 'Elementary',
            default => null,
        };
    }
}
