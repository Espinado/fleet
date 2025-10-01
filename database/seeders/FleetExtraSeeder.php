<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
use Carbon\Carbon;

class FleetExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === Drivers ===
        Driver::factory()->count(5)->create()->each(function ($driver) {
            $driver->update([
                'pers_code' => $this->generatePersCode(),
                'photo' => 'photos/drivers/'.$driver->id.'.jpg',
                'license_photo' => 'photos/licenses/'.$driver->id.'.jpg',
                'medical_certificate_photo' => 'photos/medicals/'.$driver->id.'.jpg',
                'medical_exam_passed' => $this->randomPastDate(),
                'medical_exam_expired' => $this->randomFutureDate(),
            ]);
        });

        // === Trucks ===
        Truck::all()->each(function ($truck) {
            $truck->update([
                'tech_passport_nr' => 'TP-' . rand(10000, 99999),
                'tech_passport_issued' => $this->randomPastDate(),
                'tech_passport_expired' => $this->randomFutureDate(),
                'tech_passport_photo' => 'photos/tech_passports/trucks/'.$truck->id.'.jpg',
            ]);
        });

        // === Trailers ===
        Trailer::all()->each(function ($trailer) {
            $trailer->update([
                'tech_passport_nr' => 'TP-' . rand(10000, 99999),
                'tech_passport_issued' => $this->randomPastDate(),
                'tech_passport_expired' => $this->randomFutureDate(),
                'tech_passport_photo' => 'photos/tech_passports/trailers/'.$trailer->id.'.jpg',
            ]);
        });
    }

     private function generatePersCode()
    {
        $date = Carbon::createFromTimestamp(rand(
            Carbon::create(1970, 1, 1)->timestamp,
            Carbon::create(2000, 12, 31)->timestamp
        ))->format('dmy');

        $random = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        return $date . '-' . $random;
    }

    private function randomPastDate()
    {
        // от 1 месяца назад до вчерашнего дня
        return Carbon::now()->subDays(rand(1, 30))->toDateString();
    }

    private function randomFutureDate()
    {
        // от сегодня до 4 месяцев
        return Carbon::now()->addDays(rand(0, 120))->toDateString();
    }
}
