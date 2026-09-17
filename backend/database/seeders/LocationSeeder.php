<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Seeds Caraga Region (Region XIII) provinces, cities/municipalities, and
 * their official LGU income classification (DOF/DBM local government income
 * classification, per Republic Act 11550 and related DOF orders). This is
 * real public administrative reference data used to populate the Advanced
 * Search filter dropdowns — not fabricated trainee data.
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Agusan del Norte
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Butuan City', 'lgu_income_class' => '1st Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Cabadbaran City', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Buenavista', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Carmen', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Jabonga', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Kitcharao', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Las Nieves', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Magallanes', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Nasipit', 'lgu_income_class' => '2nd Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Remedios T. Romualdez', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Santiago', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Norte', 'city_municipality' => 'Tubay', 'lgu_income_class' => '4th Class'],

            // Agusan del Sur
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Bayugan City', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Bunawan', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Esperanza', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'La Paz', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Loreto', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Prosperidad', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Rosario', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'San Francisco', 'lgu_income_class' => '2nd Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'San Luis', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Santa Josefa', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Sibagat', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Talacogon', 'lgu_income_class' => '5th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Trento', 'lgu_income_class' => '4th Class'],
            ['province' => 'Agusan del Sur', 'city_municipality' => 'Veruela', 'lgu_income_class' => '4th Class'],

            // Surigao del Norte
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Surigao City', 'lgu_income_class' => '2nd Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Alegria', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Bacuag', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Burgos', 'lgu_income_class' => '6th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Claver', 'lgu_income_class' => '2nd Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Dapa', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Del Carmen', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'General Luna', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Gigaquit', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Mainit', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Malimono', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Pilar', 'lgu_income_class' => '6th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Placer', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'San Benito', 'lgu_income_class' => '6th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'San Francisco', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'San Isidro', 'lgu_income_class' => '6th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Santa Monica', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Sison', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Socorro', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Tagana-an', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Norte', 'city_municipality' => 'Tubod', 'lgu_income_class' => '6th Class'],

            // Surigao del Sur
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Tandag City', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Bislig City', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Barobo', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Bayabas', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Cagwait', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Cantilan', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Carrascal', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Cortes', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Hinatuan', 'lgu_income_class' => '3rd Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Lanuza', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Lianga', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Lingig', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Marihatag', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'San Agustin', 'lgu_income_class' => '5th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'San Miguel', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Tagbina', 'lgu_income_class' => '4th Class'],
            ['province' => 'Surigao del Sur', 'city_municipality' => 'Tago', 'lgu_income_class' => '4th Class'],

            // Dinagat Islands
            ['province' => 'Dinagat Islands', 'city_municipality' => 'San Jose', 'lgu_income_class' => '4th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Basilisa', 'lgu_income_class' => '5th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Cagdianao', 'lgu_income_class' => '5th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Dinagat', 'lgu_income_class' => '6th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Libjo', 'lgu_income_class' => '5th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Loreto', 'lgu_income_class' => '5th Class'],
            ['province' => 'Dinagat Islands', 'city_municipality' => 'Tubajon', 'lgu_income_class' => '6th Class'],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['province' => $location['province'], 'city_municipality' => $location['city_municipality']],
                ['region' => 'Caraga', 'lgu_income_class' => $location['lgu_income_class']]
            );
        }
    }
}
