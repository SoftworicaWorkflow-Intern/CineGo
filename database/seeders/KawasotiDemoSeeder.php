<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\City;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Database\Seeder;

class KawasotiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::query()->firstOrCreate(
            ['slug' => 'kawasoti'],
            [
                'name' => 'Kawasoti',
                'name_np' => 'कावासोती',
                'is_active' => true,
                'sort_order' => 7,
            ],
        );

        $cinema = Cinema::query()->firstOrCreate(
            ['slug' => 'cinego-kawasoti-demo'],
            [
                'city_id' => $city->id,
                'name' => 'CineGo Kawasoti (Demo)',
                'name_np' => 'सिनिगो कावासोती (डेमो)',
                'address' => 'Kawasoti, Nepal (Demo location)',
                'address_np' => 'कावासोती, नेपाल (डेमो स्थान)',
                'description' => 'Demo cinema branch for sample showtimes; venue details are not verified.',
                'description_np' => 'नमुना प्रदर्शन समयका लागि डेमो सिनेमा शाखा; स्थान विवरण प्रमाणित गरिएको छैन।',
                'facilities' => ['Parking', 'Snacks', 'Air Conditioned'],
                'is_active' => true,
            ],
        );

        $hall = Hall::query()->firstOrCreate(
            [
                'cinema_id' => $cinema->id,
                'name' => 'Hall 1',
            ],
            [
                'total_rows' => 8,
                'seats_per_row' => 14,
                'is_active' => true,
            ],
        );

        if ($hall->seats()->doesntExist()) {
            $this->createSeats($hall);
        }

        $movie = Movie::query()->where('slug', 'chhaya-bazaar')->firstOrFail();
        $showDate = '2026-12-08';
        $existingShowtimes = Showtime::query()
            ->where('movie_id', $movie->id)
            ->where('cinema_id', $cinema->id)
            ->where('hall_id', $hall->id)
            ->get()
            ->mapWithKeys(fn (Showtime $showtime): array => [
                $showtime->show_date->toDateString().' '.$showtime->start_time => true,
            ]);

        foreach (['10:30:00', '13:15:00', '16:00:00', '19:00:00', '21:30:00'] as $startTime) {
            $key = $showDate.' '.$startTime;

            if ($existingShowtimes->has($key)) {
                continue;
            }

            Showtime::query()->create([
                'movie_id' => $movie->id,
                'cinema_id' => $cinema->id,
                'hall_id' => $hall->id,
                'show_date' => $showDate,
                'start_time' => $startTime,
                'format' => '2D',
                'language' => $movie->language ?? 'Nepali',
                'price_normal' => 450,
                'price_premium' => 650,
                'price_recliner' => 850,
                'is_active' => true,
            ]);

            $existingShowtimes->put($key, true);
        }
    }

    private function createSeats(Hall $hall): void
    {
        $seats = [];

        for ($row = 0; $row < $hall->total_rows; $row++) {
            $seatType = match (true) {
                $row === $hall->total_rows - 1 => 'recliner',
                $row === $hall->total_rows - 2 => 'premium',
                default => 'normal',
            };

            for ($number = 1; $number <= $hall->seats_per_row; $number++) {
                $seats[] = [
                    'hall_id' => $hall->id,
                    'row_label' => chr(65 + $row),
                    'seat_number' => $number,
                    'seat_type' => $seatType,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Seat::query()->insert($seats);
    }
}
