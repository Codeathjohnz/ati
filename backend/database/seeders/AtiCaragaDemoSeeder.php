<?php

namespace Database\Seeders;

use App\Models\DocumentSubmission;
use App\Models\Location;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingConfirmation;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Populates the system with realistic Philippine agricultural-training demo
 * content — real Caraga locations (already seeded), real ATI-style training
 * topics, and a spread of trainees with varied attendance so the Document
 * Status Tracker and Competency Gap Analysis have something genuine to
 * compute against. Everything here is written through Eloquent into MySQL;
 * the React front end still reads all of it live from the API — nothing is
 * hardcoded into the UI. Safe to re-run: it upserts by natural keys and
 * never touches accounts or records created outside this seeder.
 *
 * Run with: php artisan db:seed --class=AtiCaragaDemoSeeder
 */
class AtiCaragaDemoSeeder extends Seeder
{
    private array $programs = [];

    private array $trainees = [];

    public function run(): void
    {
        $officers = $this->seedProjectOfficers();
        $this->seedTrainingPrograms($officers);
        $this->seedTrainees($officers);
        $this->seedTrainingHistoryAndDocuments($officers);
        $this->seedUpcomingConfirmations();
        $this->linkSampleTraineeLogins();

        $this->command?->info('ATI-Caraga demo data seeded: '
            .count($this->programs).' training programs, '
            .count($this->trainees).' trainees, '
            .TrainingHistory::count().' attendance records, '
            .DocumentSubmission::count().' document status entries.');
    }

    private function seedProjectOfficers(): array
    {
        $officers = [
            ['name' => 'Maricel D. Nacario', 'username' => 'mnacario', 'email' => 'mnacario@ati-caraga.local'],
            ['name' => 'Rodel S. Amora', 'username' => 'ramora', 'email' => 'ramora@ati-caraga.local'],
        ];

        return collect($officers)->map(function (array $o) {
            return User::updateOrCreate(
                ['username' => $o['username']],
                [
                    'name' => $o['name'],
                    'email' => $o['email'],
                    'role' => User::ROLE_PROJECT_OFFICER,
                    'is_active' => true,
                    'password' => Hash::make('Officer@12345'),
                ]
            );
        })->all();
    }

    private function seedTrainingPrograms(array $officers): void
    {
        $today = now();

        $definitions = [
            // Chain Level: Production
            ['name' => 'Organic Vegetable Production Training', 'chain_level' => 'Production', 'offset' => -150, 'span' => 3, 'status' => 'completed', 'venue' => 'ATI-Caraga Training Center, Butuan City'],
            ['name' => 'Rice Seed Production and Certification Course', 'chain_level' => 'Production', 'offset' => -95, 'span' => 4, 'status' => 'completed', 'venue' => 'Municipal Agriculture Office, San Francisco, Agusan del Sur'],
            ['name' => 'Integrated Pest Management for Rice Farmers', 'chain_level' => 'Production', 'offset' => -40, 'span' => 2, 'status' => 'completed', 'venue' => 'Barangay Hall, Bayugan City'],
            ['name' => 'Native Chicken and Free-Range Poultry Raising', 'chain_level' => 'Production', 'offset' => 10, 'span' => 2, 'status' => 'ongoing', 'venue' => 'ATI-Caraga Training Center, Butuan City'],

            // Chain Level: Post-Harvest
            ['name' => 'Post-Harvest Handling and Storage of Palay', 'chain_level' => 'Post-Harvest', 'offset' => -120, 'span' => 2, 'status' => 'completed', 'venue' => 'Municipal Agriculture Office, Prosperidad, Agusan del Sur'],
            ['name' => 'Proper Handling and Grading of Fresh Produce', 'chain_level' => 'Post-Harvest', 'offset' => -60, 'span' => 2, 'status' => 'completed', 'venue' => 'ATI-Caraga Training Center, Butuan City'],

            // Chain Level: Processing
            ['name' => 'Food Processing and Preservation Techniques', 'chain_level' => 'Processing', 'offset' => -80, 'span' => 3, 'status' => 'completed', 'venue' => 'ATI-Caraga Training Center, Butuan City'],
            ['name' => 'Virgin Coconut Oil (VCO) Processing Training', 'chain_level' => 'Processing', 'offset' => -30, 'span' => 2, 'status' => 'completed', 'venue' => 'Municipal Agriculture Office, Cabadbaran City'],
            ['name' => 'Cacao Fermentation and Processing Workshop', 'chain_level' => 'Processing', 'offset' => 25, 'span' => 3, 'status' => 'upcoming', 'venue' => 'Municipal Agriculture Office, San Francisco, Agusan del Sur'],

            // Chain Level: Marketing
            ['name' => 'Agri-Enterprise Development and Marketing Seminar', 'chain_level' => 'Marketing', 'offset' => -50, 'span' => 2, 'status' => 'completed', 'venue' => 'ATI-Caraga Training Center, Butuan City'],
            ['name' => 'Digital Marketing for Farm Products', 'chain_level' => 'Marketing', 'offset' => 45, 'span' => 1, 'status' => 'upcoming', 'venue' => 'ATI-Caraga Training Center, Butuan City'],

            // Chain Level: Consumption
            ['name' => 'Nutrition and Food Safety Awareness Training', 'chain_level' => 'Consumption', 'offset' => -20, 'span' => 1, 'status' => 'completed', 'venue' => 'Barangay Hall, Tandag City'],
            ['name' => 'Household Food Security and Healthy Eating Seminar', 'chain_level' => 'Consumption', 'offset' => -200, 'span' => 1, 'status' => 'cancelled', 'venue' => 'Municipal Agriculture Office, Bislig City'],
        ];

        foreach ($definitions as $i => $def) {
            $start = $today->copy()->addDays($def['offset']);
            $program = TrainingProgram::updateOrCreate(
                ['name' => $def['name']],
                [
                    'description' => "A regional ATI-Caraga capacity-building activity under the {$def['chain_level']} stage of the agricultural value chain.",
                    'chain_level' => $def['chain_level'],
                    'venue' => $def['venue'],
                    'start_date' => $start->toDateString(),
                    'end_date' => $start->copy()->addDays($def['span'])->toDateString(),
                    'status' => $def['status'],
                    'created_by' => $officers[$i % count($officers)]->id,
                ]
            );
            $this->programs[] = $program;
        }
    }

    private function seedTrainees(array $officers): void
    {
        $maleFirstNames = ['Juan', 'Pedro', 'Ramon', 'Jose', 'Antonio', 'Ernesto', 'Ricardo', 'Rodolfo', 'Domingo', 'Federico', 'Teodoro', 'Bonifacio', 'Marcelo', 'Leonardo', 'Reynaldo'];
        $femaleFirstNames = ['Maria', 'Rosario', 'Carmela', 'Corazon', 'Josefina', 'Remedios', 'Teresita', 'Gloria', 'Perla', 'Luzviminda', 'Evangeline', 'Milagros', 'Adoracion', 'Consolacion', 'Salvacion'];
        $surnames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Ramos', 'Aquino', 'Villanueva', 'Fernandez', 'Aguilar', 'Domingo', 'Salazar', 'Navarro', 'Ocampo', 'Rivera', 'Castillo', 'Pascual', 'Rosales', 'Gonzales', 'Manalo', 'Bernardo', 'Lozada'];
        $organizations = [
            'Barangay Agricultural Extension Workers Association', 'Municipal Agriculture Office', 'Rural Improvement Club',
            '4-H Club Caraga Chapter', 'Irrigators Association', "Women's Agri-Livelihood Association",
            'Young Farmers Federation', "Farmers' Multipurpose Cooperative", 'Cacao Growers Association',
            'Coconut Farmers Cooperative', 'Independent Farmer',
        ];
        $educationLevels = ['Elementary', 'High School', 'High School', 'Vocational', 'Vocational', 'College', 'College', 'Post Graduate'];

        $locations = Location::all();

        $count = 26;
        for ($i = 0; $i < $count; $i++) {
            $isMale = $i % 2 === 0;
            $first = $isMale ? $maleFirstNames[$i % count($maleFirstNames)] : $femaleFirstNames[$i % count($femaleFirstNames)];
            $last = $surnames[($i * 3 + 5) % count($surnames)];
            $location = $locations[$i % $locations->count()];
            $age = 22 + (($i * 7) % 40); // spread ages 22–61
            $email = strtolower($first).'.'.strtolower(str_replace(' ', '', $last)).$i.'@gmail.com';

            $trainee = TraineeProfile::updateOrCreate(
                ['first_name' => $first, 'last_name' => $last, 'email' => $email],
                [
                    'sex' => $isMale ? 'Male' : 'Female',
                    'birthdate' => now()->subYears($age)->subDays($i * 11)->toDateString(),
                    'contact_number' => '09'.str_pad((string) (170000000 + $i * 137), 9, '0', STR_PAD_LEFT),
                    'address_line' => 'Purok '.(1 + $i % 6).', Barangay Poblacion',
                    'barangay' => 'Poblacion',
                    'city_municipality' => $location->city_municipality,
                    'province' => $location->province,
                    'region' => 'Caraga',
                    'lgu_income_class' => $location->lgu_income_class,
                    'educational_level' => $educationLevels[$i % count($educationLevels)],
                    'organization_affiliation' => $organizations[$i % count($organizations)],
                    'occupation' => $isMale ? 'Farmer' : 'Agricultural Worker',
                    'created_by' => $officers[$i % count($officers)]->id,
                ]
            );
            $this->trainees[] = $trainee;
        }
    }

    private function seedTrainingHistoryAndDocuments(array $officers): void
    {
        $requiredDocuments = RequiredDocument::where('is_active', true)->get();
        $eligiblePrograms = collect($this->programs)->whereIn('status', ['completed', 'ongoing']);
        $programsByLevel = $eligiblePrograms->groupBy('chain_level');

        // Re-running the seeder should regenerate attendance/documents for
        // these exact demo trainees (their IDs are stable — seedTrainees()
        // upserts by name+email) rather than silently skip everything via
        // firstOrCreate, which would leave a stale, previously-random
        // compliance mix in place. This never touches any other trainee.
        $demoTraineeIds = collect($this->trainees)->pluck('id');
        DocumentSubmission::whereIn('trainee_id', $demoTraineeIds)->delete();
        TrainingHistory::whereIn('trainee_id', $demoTraineeIds)->delete();

        foreach ($this->trainees as $i => $trainee) {
            // Vary each trainee's completion profile so the gap analysis and
            // compliance reports show a realistic mix rather than one pattern.
            $profile = $i % 4; // 0: full completion, 1-2: partial, 3: no history yet
            $levelsToAttend = match ($profile) {
                0 => $programsByLevel->keys()->all(),                          // completes every chain level
                1 => $programsByLevel->keys()->random(3)->all(),               // three levels
                2 => $programsByLevel->keys()->random(2)->all(),               // two levels
                default => [],                                                 // brand-new registrant, no training yet
            };

            // Document compliance is decided per TRAINEE, not per document roll:
            // real trainees are consistently on top of their paperwork or
            // consistently behind on it — rolling each of the 6 required
            // documents independently at a flat rate made even diligent
            // trainees fail almost every training by chance (6 independent
            // 78%-likely rolls only land all-Yes ~22% of the time). Trainee 0
            // is the "model trainee" showcase account and is guaranteed clean.
            $isShowcaseTrainee = $i === 0;
            $isDiligent = ($i % 10) < 7; // ~70% of the general population is diligent
            $perDocumentSubmissionRate = $isDiligent ? 96 : 40;

            foreach ($levelsToAttend as $level) {
                $program = $programsByLevel[$level]->random();

                $history = TrainingHistory::firstOrCreate(
                    ['trainee_id' => $trainee->id, 'training_program_id' => $program->id],
                    [
                        'chain_level' => $program->chain_level,
                        'date_attended' => $program->start_date,
                        'remarks' => null,
                        'created_by' => $officers[$i % count($officers)]->id,
                    ]
                );

                foreach ($requiredDocuments as $doc) {
                    // Deterministic for the showcase trainee — it exists specifically to
                    // demo the fully-compliant state, so it must never randomly miss one.
                    $submitted = $isShowcaseTrainee || fake()->boolean($perDocumentSubmissionRate);

                    DocumentSubmission::firstOrCreate(
                        [
                            'trainee_id' => $trainee->id,
                            'training_history_id' => $history->id,
                            'required_document_id' => $doc->id,
                        ],
                        [
                            'submitted' => $submitted,
                            'date_submitted' => $submitted ? $program->start_date : null,
                            'updated_by' => $officers[$i % count($officers)]->id,
                        ]
                    );
                }
            }
        }
    }

    private function seedUpcomingConfirmations(): void
    {
        $upcoming = collect($this->programs)->whereIn('status', ['upcoming', 'ongoing']);
        $statuses = ['pending', 'confirmed', 'declined', 'confirmed', 'pending'];

        foreach ($upcoming as $program) {
            foreach (array_slice($this->trainees, 0, 12) as $i => $trainee) {
                TrainingConfirmation::firstOrCreate(
                    ['trainee_id' => $trainee->id, 'training_program_id' => $program->id],
                    [
                        'status' => $statuses[$i % count($statuses)],
                        'confirmed_at' => $statuses[$i % count($statuses)] !== 'pending' ? now() : null,
                    ]
                );
            }
        }
    }

    /**
     * Links portal login access to a handful of trainees chosen to showcase
     * the different states: full chain-level completion, partial, and none.
     */
    private function linkSampleTraineeLogins(): void
    {
        $samples = [
            0 => 'mabini.santos',   // profile 0: completes every chain level
            1 => 'lopez.reyes',     // profile 1: partial (3 levels)
            3 => 'delacruz.torres', // profile 3: no training history yet
        ];

        foreach ($samples as $index => $username) {
            $trainee = $this->trainees[$index] ?? null;
            if (! $trainee || $trainee->user_id) {
                continue;
            }

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $trainee->full_name,
                    'email' => $trainee->email ?? $username.'@ati-caraga.local',
                    'role' => User::ROLE_TRAINEE,
                    'is_active' => true,
                    'password' => Hash::make('Trainee@12345'),
                ]
            );

            $trainee->update(['user_id' => $user->id]);
        }
    }
}
