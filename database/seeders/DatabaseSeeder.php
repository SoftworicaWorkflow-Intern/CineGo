<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Cinema;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<int, User> */
    private array $users = [];

    /** @var array<int, Movie> */
    private array $movies = [];

    /** @var array<int, Cinema> */
    private array $cinemas = [];

    /** @var list<Showtime> */
    private array $showtimes = [];

    /**
     * Seed the application's database with a complete, realistic CineGo demo.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedCities();
        $this->seedCinemas();
        $this->seedMovies();
        $this->seedShowtimes();
        $this->seedOffers();
        $this->seedAnnouncements();
        $this->seedReviews();
        $this->seedContactMessages();
        $this->seedDemoBookings();
    }

    private function seedUsers(): void
    {
        $this->users[] = User::factory()->create([
            'name' => 'CineGo Admin',
            'email' => 'admin@cinego.com.np',
            'phone' => '9800000001',
            'password' => Hash::make('password'),
            'preferred_language' => 'en',
            'is_admin' => true,
        ]);

        $this->users[] = User::factory()->create([
            'name' => 'Shreeja Gurung',
            'email' => 'shreeja@example.com',
            'phone' => '9800000002',
            'password' => Hash::make('password'),
            'preferred_language' => 'en',
        ]);

        $this->users[] = User::factory()->create([
            'name' => 'Aayush Thapa',
            'email' => 'aayush@example.com',
            'phone' => '9800000003',
            'password' => Hash::make('password'),
            'preferred_language' => 'np',
        ]);
    }

    private function seedCities(): void
    {
        $cities = [
            ['name' => 'Kathmandu', 'name_np' => 'काठमाडौं', 'sort_order' => 1],
            ['name' => 'Lalitpur', 'name_np' => 'ललितपुर', 'sort_order' => 2],
            ['name' => 'Pokhara', 'name_np' => 'पोखरा', 'sort_order' => 3],
            ['name' => 'Bharatpur', 'name_np' => 'भरतपुर', 'sort_order' => 4],
            ['name' => 'Butwal', 'name_np' => 'बुटवल', 'sort_order' => 5],
            ['name' => 'Biratnagar', 'name_np' => 'विराटनगर', 'sort_order' => 6],
        ];

        foreach ($cities as $city) {
            City::create($city + ['slug' => Str::slug($city['name']), 'is_active' => true]);
        }
    }

    private function seedCinemas(): void
    {
        $branches = [
            [
                'city' => 'Lalitpur',
                'name' => 'CineGo Jhamsikhel',
                'name_np' => 'सिनिगो जम्सिखेल',
                'address' => 'Jhamsikhel Road, Lalitpur',
                'address_np' => 'जम्सिखेल रोड, ललितपुर',
                'phone' => '+977-1-5550101',
                'email' => 'jhamsikhel@cinego.com.np',
                'facilities' => ['Parking', 'Snacks', 'Air Conditioned', 'Wheelchair Access'],
                'banner' => '/img/banners/banner-1.svg',
                'halls' => [['Hall 1', 8, 14], ['Hall 2', 7, 12], ['Hall 3', 6, 10]],
            ],
            [
                'city' => 'Kathmandu',
                'name' => 'CineGo Durbarmarg',
                'name_np' => 'सिनिगो दरबारमार्ग',
                'address' => 'Durbarmarg, Kathmandu',
                'address_np' => 'दरबारमार्ग, काठमाडौं',
                'phone' => '+977-1-5550202',
                'email' => 'durbarmarg@cinego.com.np',
                'facilities' => ['Parking', 'IMAX', 'Food Court', 'Air Conditioned'],
                'banner' => '/img/banners/banner-2.svg',
                'halls' => [['IMAX Hall', 9, 18], ['Hall 2', 8, 14], ['Hall 3', 7, 12]],
            ],
            [
                'city' => 'Kathmandu',
                'name' => 'CineGo Thamel',
                'name_np' => 'सिनिगो थमेल',
                'address' => 'Chaksibari Marg, Thamel, Kathmandu',
                'address_np' => 'चक्षिबारी मार्ग, थमेल, काठमाडौं',
                'phone' => '+977-1-5550303',
                'email' => 'thamel@cinego.com.np',
                'facilities' => ['Snacks', 'Air Conditioned', 'Student Discount'],
                'banner' => '/img/banners/banner-3.svg',
                'halls' => [['Hall 1', 7, 12], ['Hall 2', 6, 11]],
            ],
            [
                'city' => 'Pokhara',
                'name' => 'CineGo Lakeside',
                'name_np' => 'सिनिगो लेकसाइड',
                'address' => 'Lakeside Road, Pokhara',
                'address_np' => 'लेकसाइड रोड, पोखरा',
                'phone' => '+977-61-5550404',
                'email' => 'lakeside@cinego.com.np',
                'facilities' => ['Parking', 'Lake View', 'Snacks', 'Air Conditioned'],
                'banner' => '/img/banners/banner-4.svg',
                'halls' => [['Hall 1', 8, 13], ['Hall 2', 6, 12]],
            ],
            [
                'city' => 'Bharatpur',
                'name' => 'CineGo Bharatpur',
                'name_np' => 'सिनिगो भरतपुर',
                'address' => 'Bharatpur-10, Chitwan',
                'address_np' => 'भरतपुर-१०, चितवन',
                'phone' => '+977-56-5550505',
                'email' => 'bharatpur@cinego.com.np',
                'facilities' => ['Parking', 'Snacks', 'Air Conditioned'],
                'banner' => '/img/banners/banner-5.svg',
                'halls' => [['Hall 1', 7, 12], ['Hall 2', 6, 10]],
            ],
            [
                'city' => 'Butwal',
                'name' => 'CineGo Butwal',
                'name_np' => 'सिनिगो बुटवल',
                'address' => 'Traffic Chowk, Butwal',
                'address_np' => 'ट्राफिक चोक, बुटवल',
                'phone' => '+977-71-5550606',
                'email' => 'butwal@cinego.com.np',
                'facilities' => ['Parking', 'Snacks', 'Wheelchair Access'],
                'banner' => '/img/banners/banner-6.svg',
                'halls' => [['Hall 1', 7, 12]],
            ],
        ];

        foreach ($branches as $index => $branch) {
            $city = City::where('slug', Str::slug($branch['city']))->firstOrFail();

            $cinema = Cinema::create([
                'city_id' => $city->id,
                'name' => $branch['name'],
                'name_np' => $branch['name_np'],
                'slug' => Str::slug($branch['name']),
                'address' => $branch['address'],
                'address_np' => $branch['address_np'],
                'phone' => $branch['phone'],
                'email' => $branch['email'],
                'description' => 'CineGo '.$branch['city'].' is part of Nepal\'s homegrown cinema chain — crystal-clear projection, comfortable seats and the warmest hospitality this side of the Himalayas.',
                'description_np' => $branch['name_np'].' नेपालको आफ्नै सिनेमा शृंखलाको एक शाखा हो — सफा प्रोजेक्सन, आरामदायी सिट र उच्च गुणस्तरको सेवा।',
                'facilities' => $branch['facilities'],
                'image_url' => $branch['banner'],
                'is_active' => true,
            ]);

            foreach ($branch['halls'] as [$hallName, $rows, $seatsPerRow]) {
                $hall = Hall::create([
                    'cinema_id' => $cinema->id,
                    'name' => $hallName,
                    'total_rows' => $rows,
                    'seats_per_row' => $seatsPerRow,
                    'is_active' => true,
                ]);

                $this->generateSeats($hall);
            }

            $this->cinemas[] = $cinema->load('halls');
        }
    }

    /**
     * Mirrors the seat layout rules used by the admin screen editor:
     * last row = recliner, second last = premium, everything else normal.
     */
    private function generateSeats(Hall $hall): void
    {
        $now = now();
        $seats = [];

        for ($row = 0; $row < $hall->total_rows; $row++) {
            $label = chr(65 + $row);
            $type = match (true) {
                $row === $hall->total_rows - 1 => 'recliner',
                $row === $hall->total_rows - 2 => 'premium',
                default => 'normal',
            };

            for ($number = 1; $number <= $hall->seats_per_row; $number++) {
                $seats[] = [
                    'hall_id' => $hall->id,
                    'row_label' => $label,
                    'seat_number' => $number,
                    'seat_type' => $type,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Seat::insert($seats);
    }

    private function seedMovies(): void
    {
        $movies = [
            [
                'title' => 'Kawasoti Nights',
                'title_np' => 'कावासोती नाइट्स',
                'synopsis' => 'A relentless crime reporter and a street-smart detective race through Kawasoti\'s neon alleys to expose a smuggling ring before the city wakes up.',
                'synopsis_np' => 'संवाददाता र पुलिस अधिकारी कावासोतीका नियोन गल्लीहरूमा शहर जाग्नुअघि तस्करी गिरोह पर्दाफास गर्न दौडिन्छन्।',
                'genres' => ['Thriller', 'Action'],
                'duration_minutes' => 128,
                'language' => 'Nepali',
                'release_date' => now()->subDays(12)->toDateString(),
                'certificate' => 'UA',
                'rating' => 8.1,
                'poster' => 1,
                'banner' => 1,
                'trailer' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
                'director' => 'Bibek Acharya',
                'cast' => 'Prakash Lamsal, Anjana Rai, Sujan Karki',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Himalayan Echo',
                'title_np' => 'हिमालयन इको',
                'synopsis' => 'A Sherpa guide and a stranded musician find an unlikely friendship on a frozen pass — and a melody that could bring a village back to life.',
                'synopsis_np' => 'हिमाल निकासीमा एक शेर्पा गाइड र संगीतकारबीच अप्रत्याशित साथ जन्मन्छ, जसले गाउँलाई फेरि जिउँदो बनाउँछ।',
                'genres' => ['Drama', 'Adventure'],
                'duration_minutes' => 116,
                'language' => 'Nepali',
                'release_date' => now()->subDays(5)->toDateString(),
                'certificate' => 'U',
                'rating' => 8.4,
                'poster' => 2,
                'banner' => 5,
                'trailer' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
                'director' => 'Mingma Sherpa',
                'cast' => 'Dawa Lama, Nima Yaden, Gita Kunwar',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'The Last Yatra',
                'title_np' => 'अन्तिम यात्रा',
                'synopsis' => 'Three generations set out on one final pilgrimage across the Terai, carrying a secret that could split the family apart.',
                'synopsis_np' => 'तीन पुस्ताले एक अन्तिम तीर्थयात्रा निकाल्छन्, जसमा लुकेको रहस्यले परिवार विभाजन गर्न सक्छ।',
                'genres' => ['Drama', 'Family'],
                'duration_minutes' => 134,
                'language' => 'Nepali',
                'release_date' => now()->subDays(19)->toDateString(),
                'certificate' => 'U/A',
                'rating' => 7.6,
                'poster' => 3,
                'banner' => 6,
                'trailer' => null,
                'director' => 'Ramesh K.C.',
                'cast' => 'Rajesh Hamal, Keki Adhikari, Anmol KC',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Phir Bhi Dil Hai Nepali',
                'title_np' => 'फेरि पनि मन नेपाली',
                'synopsis' => 'A big-city chef returns to her hometown bakery and falls for the stubborn farmer who supplies her jute sugar.',
                'synopsis_np' => 'ठूलो शहरकी रसोइया आफ्नो गाउँको बेकरीमा फर्किन्छिन् र त्यहाँको कृषकसँग माया पर्छिन्।',
                'genres' => ['Romance', 'Comedy'],
                'duration_minutes' => 121,
                'language' => 'Hindi',
                'release_date' => now()->subDays(8)->toDateString(),
                'certificate' => 'U/A',
                'rating' => 7.2,
                'poster' => 4,
                'banner' => 2,
                'trailer' => null,
                'director' => 'Sana Bhattarai',
                'cast' => 'Aashika Rana, Nirajan Shrestha, Priya Koirala',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'Aankha Phuley',
                'title_np' => 'आँखा फुल्यो',
                'synopsis' => 'A blind comedian accidentally becomes the voice of a revolution — and discovers his punchlines are funnier than his sighted friends think.',
                'synopsis_np' => 'दृष्टिहीन हास्यकलाकार आकस्मिक रूपमा क्रान्तिको आवाज बन्छ र आफ्नो हास्यकला अझ रमाइलो हुने पाउँछ।',
                'genres' => ['Comedy', 'Drama'],
                'duration_minutes' => 108,
                'language' => 'Nepali',
                'release_date' => now()->subDays(2)->toDateString(),
                'certificate' => 'U',
                'rating' => 7.9,
                'poster' => 5,
                'banner' => 1,
                'trailer' => null,
                'director' => 'Sagar Poudel',
                'cast' => 'Sanjog Koirala, Barsha Siwakoti, Deepak DC',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'Bhoot Katha',
                'title_np' => 'भूत कथा',
                'synopsis' => 'A skeptical podcaster spends seven nights recording folklore in an abandoned Pati house — and the tapes start answering back.',
                'synopsis_np' => 'एक शंकालु पोडकास्टरले छाडिएको पाटीमा सात रात लोककथा रेकर्ड गर्छ, तर ट्यापले नै जवाफ दिन थाल्छन्।',
                'genres' => ['Horror', 'Thriller'],
                'duration_minutes' => 102,
                'language' => 'Nepali',
                'release_date' => now()->subDays(15)->toDateString(),
                'certificate' => 'A',
                'rating' => 7.4,
                'poster' => 6,
                'banner' => 4,
                'trailer' => null,
                'director' => 'Unish Gurung',
                'cast' => 'Salina Lamichhane, Prabin Bastola, Ritesh Rajbhandari',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => false,
                'sort_order' => 6,
            ],
            [
                'title' => 'Shikhar',
                'title_np' => 'शिखर',
                'synopsis' => 'Two rival climbers are forced to share one rope when the storm of the decade hits the world\'s unclimbed wall.',
                'synopsis_np' => 'विश्वको नचढिएको भित्तामा ठूलो आँधी लाग्दा दुई प्रतिद्वन्द्वी पर्वारोही एउटै रस्सीमा बाँधिन्छन्।',
                'genres' => ['Action', 'Adventure'],
                'duration_minutes' => 126,
                'language' => 'Nepali',
                'release_date' => now()->subDays(22)->toDateString(),
                'certificate' => 'U/A',
                'rating' => 8.0,
                'poster' => 7,
                'banner' => 3,
                'trailer' => 'https://www.youtube.com/embed/aqz-KE-bpKQ',
                'director' => 'Prashant Shrestha',
                'cast' => 'Wilson Bikram Rai, Aanchal Sharma, Suraj Paudel',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => false,
                'sort_order' => 7,
            ],
            [
                'title' => 'Maya Django',
                'title_np' => 'माया डेन्जो',
                'synopsis' => 'A retired smuggler-turned-taxi driver has one night to repay a debt to the very gang that destroyed his family.',
                'synopsis_np' => 'सेवानिवृत्त तस्कर ट्याक्सी चालकले आफ्नो परिवार नष्ट गर्ने गिरोहलाई एक रातमै ऋण तिर्नुपर्छ।',
                'genres' => ['Action', 'Thriller'],
                'duration_minutes' => 131,
                'language' => 'Hindi',
                'release_date' => now()->subDays(27)->toDateString(),
                'certificate' => 'A',
                'rating' => 7.7,
                'poster' => 8,
                'banner' => 2,
                'trailer' => null,
                'director' => 'Vikas Rane',
                'cast' => 'Karan Bista, Shweta Khadka, Ravi Jha',
                'now_showing' => true,
                'coming_soon' => false,
                'featured' => false,
                'sort_order' => 8,
            ],
            [
                'title' => 'Rani of Mustang',
                'title_np' => 'मुस्ताङकी रानी',
                'synopsis' => 'A young queen defends her mountain kingdom with nothing but wit, wine and a very stubborn yak army.',
                'synopsis_np' => 'युवा रानीले चतुराइ, मदिरा र जिद्दी याक सेनाले आफ्नो हिमालयी राज्य जोगाउँछिन्।',
                'genres' => ['Adventure', 'Drama'],
                'duration_minutes' => 119,
                'language' => 'Nepali',
                'release_date' => now()->addDays(14)->toDateString(),
                'certificate' => 'U',
                'rating' => 7.5,
                'poster' => 2,
                'banner' => 5,
                'trailer' => null,
                'director' => 'Tenzin Lama',
                'cast' => 'Pemba Sherpa, Yolma Lama, Kaji Tamang',
                'now_showing' => false,
                'coming_soon' => true,
                'featured' => false,
                'sort_order' => 9,
            ],
            [
                'title' => 'Chhaya Bazaar',
                'title_np' => 'छायाँ बजार',
                'synopsis' => 'A puppeteer inherits a cursed shop in Asan where every doll remembers a crime nobody reported.',
                'synopsis_np' => 'असनको एक कोसेढुङ्गामा कुखुरा घर नै विरासतमा पाउँछ, जहाँ हरेक गुड्डाले अपराध सम्झन्छ।',
                'genres' => ['Mystery', 'Horror'],
                'duration_minutes' => 111,
                'language' => 'Nepali',
                'release_date' => now()->addDays(28)->toDateString(),
                'certificate' => 'U/A',
                'rating' => 7.1,
                'poster' => 6,
                'banner' => 4,
                'trailer' => null,
                'director' => 'Rojan Shrestha',
                'cast' => 'Menuka Pradhan, Susan Chaudhari, Bipin Thapa',
                'now_showing' => false,
                'coming_soon' => true,
                'featured' => false,
                'sort_order' => 10,
            ],
            [
                'title' => 'Goal Ghar',
                'title_np' => 'गोल घर',
                'synopsis' => 'A village side from the hills gets one shot at the national cup, and their goalkeeper has never worn shoes.',
                'synopsis_np' => 'पहाडको गाउँले राष्ट्रिय कपको एक अवसर पाउँछ, र उनीहरूको गोलकिपरले कहिल्यै जुत्ता लगाएको छैन।',
                'genres' => ['Drama', 'Sport'],
                'duration_minutes' => 124,
                'language' => 'Nepali',
                'release_date' => now()->addDays(42)->toDateString(),
                'certificate' => 'U',
                'rating' => 8.2,
                'poster' => 7,
                'banner' => 6,
                'trailer' => null,
                'director' => 'Nischal Basnet',
                'cast' => 'Dayahang Rai, Rekha Thapa, Swastima Khadka',
                'now_showing' => false,
                'coming_soon' => true,
                'featured' => false,
                'sort_order' => 11,
            ],
            [
                'title' => 'Ani, Timi',
                'title_np' => 'अनि, तिमी',
                'synopsis' => 'Two pen pals who never met plan a final monsoon date at the very spot their letters began.',
                'synopsis_np' => 'कहिल्यै भेटिनु भएन दुई चिठीपत्री साथीहरू आफ्नो चिठी सुरु भएकै ठाउँमा अन्तिम वर्षामा भेट्ने योजना बनाउँछन्।',
                'genres' => ['Romance', 'Drama'],
                'duration_minutes' => 113,
                'language' => 'Nepali',
                'release_date' => now()->addDays(56)->toDateString(),
                'certificate' => 'U/A',
                'rating' => 7.8,
                'poster' => 4,
                'banner' => 3,
                'trailer' => null,
                'director' => 'Aashish Bhandari',
                'cast' => 'Miruna Magar, Suraj Singh Thakuri, Eve Alvarez',
                'now_showing' => false,
                'coming_soon' => true,
                'featured' => false,
                'sort_order' => 12,
            ],
        ];

        foreach ($movies as $movie) {
            $model = Movie::create([
                'title' => $movie['title'],
                'title_np' => $movie['title_np'],
                'slug' => Str::slug($movie['title']),
                'synopsis' => $movie['synopsis'],
                'synopsis_np' => $movie['synopsis_np'],
                'genres' => $movie['genres'],
                'duration_minutes' => $movie['duration_minutes'],
                'language' => $movie['language'],
                'release_date' => $movie['release_date'],
                'certificate' => $movie['certificate'],
                'rating' => $movie['rating'],
                'poster_url' => '/img/posters/poster-'.$movie['poster'].'.svg',
                'banner_url' => '/img/banners/banner-'.$movie['banner'].'.svg',
                'trailer_url' => $movie['trailer'],
                'director' => $movie['director'],
                'cast' => $movie['cast'],
                'is_now_showing' => $movie['now_showing'],
                'is_coming_soon' => $movie['coming_soon'],
                'is_featured' => $movie['featured'],
                'is_active' => true,
                'sort_order' => $movie['sort_order'],
            ]);

            $this->movies[] = $model;
        }
    }

    private function seedShowtimes(): void
    {
        $slots = ['10:30:00', '13:15:00', '16:00:00', '19:00:00', '21:30:00'];
        $nowShowing = collect($this->movies)->where('is_now_showing', true)->values();
        $prices = [
            'normal' => [450, 500, 550],
            'extra_premium' => 150,
            'extra_recliner' => 300,
        ];

        $movieCursor = 0;

        for ($day = 0; $day < 7; $day++) {
            $date = Date::today()->addDays($day)->toDateString();

            foreach ($this->cinemas as $cinemaIndex => $cinema) {
                foreach ($cinema->halls as $hallIndex => $hall) {
                    // Each screen gets 3 shows a day at rotating slots.
                    $slotsForHall = array_slice($slots, ($day + $hallIndex) % 3, 3);

                    foreach ($slotsForHall as $slot) {
                        $movie = $nowShowing[$movieCursor % $nowShowing->count()];
                        $movieCursor++;

                        $base = $prices['normal'][($cinemaIndex + $hallIndex) % count($prices['normal'])];

                        $this->showtimes[] = Showtime::create([
                            'movie_id' => $movie->id,
                            'cinema_id' => $cinema->id,
                            'hall_id' => $hall->id,
                            'show_date' => $date,
                            'start_time' => $slot,
                            'format' => match (true) {
                                str_contains($hall->name, 'IMAX') => 'IMAX',
                                ($day + $hallIndex) % 4 === 0 => '3D',
                                default => '2D',
                            },
                            'language' => $movie->language,
                            'price_normal' => $base,
                            'price_premium' => $base + $prices['extra_premium'],
                            'price_recliner' => $base + $prices['extra_recliner'],
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }
    }

    private function seedOffers(): void
    {
        Offer::create([
            'title' => 'Welcome Offer — 10% off your first show',
            'title_np' => 'स्वागत अफर — पहिलो स्योममा १०% छुट',
            'description' => 'New to CineGo? Use code WELCOME10 on any booking of Rs. 500 or more.',
            'description_np' => 'सिनिगोमा नयाँ हुनुहुन्छ? WELCOME10 कोड प्रयोग गर्नुहोस्।',
            'code' => 'WELCOME10',
            'badge' => 'NEW',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_amount' => 500,
            'valid_from' => now()->subDays(10)->toDateString(),
            'valid_to' => now()->addMonths(3)->toDateString(),
            'image_url' => '/img/banners/banner-2.svg',
            'is_active' => true,
        ]);

        Offer::create([
            'title' => 'Weekend Double — Buy one, get one free',
            'title_np' => 'वीकेन्ड डबल — एक लिनुहोस्, एक निःशुल्क',
            'description' => 'Every Saturday and Sunday morning show: buy one ticket, get a second of equal or lower value free. Code WEEKEND20.',
            'description_np' => 'शनि र आइतबारको बिहानको स्योममा एक टिकटसँग अर्को निःशुल्क। कोड: WEEKEND20',
            'code' => 'WEEKEND20',
            'badge' => 'HOT',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_amount' => 800,
            'valid_from' => now()->subDays(5)->toDateString(),
            'valid_to' => now()->addMonth()->toDateString(),
            'image_url' => '/img/banners/banner-1.svg',
            'is_active' => true,
        ]);

        Offer::create([
            'title' => 'Student Night — Rs. 150 off',
            'title_np' => 'विद्यार्थी रात — १५० रुपैयाँ छुट',
            'description' => 'Show any student ID at checkout with code STUDENT15 and save Rs. 150 per ticket.',
            'description_np' => 'छात्रवृत्ति कार्ड देखाएर STUDENT15 कोड प्रयोग गर्नुहोस्।',
            'code' => 'STUDENT15',
            'badge' => 'STUDENT',
            'discount_type' => 'fixed',
            'discount_value' => 150,
            'min_amount' => 400,
            'valid_from' => now()->subDays(30)->toDateString(),
            'valid_to' => now()->addMonths(6)->toDateString(),
            'image_url' => '/img/banners/banner-3.svg',
            'is_active' => true,
        ]);

        Offer::create([
            'title' => 'Late Show Saver — Rs. 100 off after 9 PM',
            'title_np' => 'हिउँदो रातको छुट — रात ९ पछि १०० रुपैयाँ छुट',
            'description' => 'Any show starting at 21:00 or later gets Rs. 100 off with code NIGHTOWL. Valid all week.',
            'description_np' => 'रात ९ बजेपछिको स्योममा NIGHTOWL कोडले १०० रुपैयाँ छुट।',
            'code' => 'NIGHTOWL',
            'badge' => 'DEALS',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'min_amount' => 300,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_to' => now()->addMonths(2)->toDateString(),
            'image_url' => '/img/banners/banner-6.svg',
            'is_active' => true,
        ]);
    }

    private function seedAnnouncements(): void
    {
        Announcement::create([
            'message' => '🎟️ Use code WELCOME10 for 10% off your first CineGo booking',
            'message_np' => '🎟️ पहिलो बुकिङमा १०% छुटका लागि WELCOME10 कोड प्रयोग गर्नुहोस्',
            'link' => '/offers',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Announcement::create([
            'message' => '🎬 Kawasoti Nights & Shikhar now playing at a screen near you',
            'message_np' => '🎬 कावासोती नाइट्स र शिखर तपाईंको नजिकैको स्क्रिनमा',
            'link' => '/movies',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Announcement::create([
            'message' => '🍿 Weekend morning shows: buy one get one free with WEEKEND20',
            'message_np' => '🍿 वीकेन्ड बिहानको स्योममा एक लिनुहोस् एक निःशुल्क — WEEKEND20',
            'link' => '/offers',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    private function seedReviews(): void
    {
        $reviews = [
            [0, 1, 5, 'Best thriller from Nepal this year', 'The night sequences in Kawasoti are shot like a dream. Kept me on the edge the whole time.'],
            [0, 2, 4, 'Great pacing', 'Second half drags a little but the climax more than makes up for it.'],
            [1, 2, 5, 'Quietly powerful', 'I did not expect a mountain film to make me cry. The soundtrack is unreal.'],
            [4, 1, 4, 'Genuinely funny', 'The blind comedian bit about traffic police had the whole hall laughing.'],
            [5, 2, 4, 'Creepy and local', 'Finally a horror film that uses our own folklore instead of imported clichés.'],
        ];

        foreach ($reviews as [$movieIndex, $userIndex, $rating, $title, $body]) {
            Review::create([
                'movie_id' => $this->movies[$movieIndex]->id,
                'user_id' => $this->users[$userIndex]->id,
                'rating' => $rating,
                'title' => $title,
                'body' => $body,
            ]);
        }
    }

    private function seedContactMessages(): void
    {
        ContactMessage::create([
            'name' => 'Nisha Shrestha',
            'email' => 'nisha@example.com',
            'phone' => '9800112233',
            'subject' => 'Group booking for 20 people',
            'message' => 'Hello, I would like to book 20 seats for Saturday 6 PM at Durbarmarg. Is there a group discount available?',
            'is_read' => false,
        ]);

        ContactMessage::create([
            'name' => 'Bikash Rai',
            'email' => 'bikash@example.com',
            'phone' => '9844556677',
            'subject' => 'Refund for cancelled show',
            'message' => 'My show was cancelled by the cinema but I have not received my refund yet. Booking code CG-DEMO1234.',
            'is_read' => false,
        ]);
    }

    /**
     * Creates a fortnight of realistic bookings so the admin dashboard,
     * revenue chart and payment audit trail all have data to show.
     */
    private function seedDemoBookings(): void
    {
        $customers = [
            ['Shreeja Gurung', 'shreeja@example.com', '9800000002'],
            ['Aayush Thapa', 'aayush@example.com', '9800000003'],
            ['Nisha Shrestha', 'nisha@example.com', '9800112233'],
            ['Bikash Rai', 'bikash@example.com', '9844556677'],
            ['Anjana Tamang', 'anjana@example.com', '9812345678'],
            ['Sujan Karki', 'sujan@example.com', '9876543210'],
            ['Prakriti Adhikari', 'prakriti@example.com', '9801112223'],
        ];

        $methods = ['esewa', 'khalti', 'card', 'counter'];
        $usedSeats = [];

        for ($i = 0; $i < 42; $i++) {
            $showtime = $this->showtimes[array_rand($this->showtimes)];
            $hall = $showtime->hall;
            $seats = $hall->seats()->where('is_active', true)->get()->shuffle()->take(rand(1, 4));

            if ($seats->isEmpty()) {
                continue;
            }

            $key = $showtime->id.':'.$seats->pluck('id')->sort()->implode('-');
            if (isset($usedSeats[$key])) {
                continue;
            }
            $usedSeats[$key] = true;

            [$customerName, $customerEmail, $customerPhone] = $customers[array_rand($customers)];
            $method = $methods[array_rand($methods)];

            $status = match (true) {
                $i % 11 === 0 => Booking::STATUS_PENDING,
                $i % 17 === 0 => Booking::STATUS_CANCELLED,
                default => Booking::STATUS_CONFIRMED,
            };

            $isPaid = $status === Booking::STATUS_CONFIRMED && $method !== 'counter'
                ? true
                : ($status === Booking::STATUS_CONFIRMED && $method === 'counter' && $i % 3 === 0);

            $subtotal = 0;
            $lines = [];

            foreach ($seats as $seat) {
                $price = match ($seat->seat_type) {
                    'premium' => $showtime->price_premium,
                    'recliner' => $showtime->price_recliner,
                    default => $showtime->price_normal,
                };
                $subtotal += $price;
                $lines[] = [$seat, $price];
            }

            $createdAt = Date::now()->subDays(rand(0, 13))->subMinutes(rand(0, 900));

            $booking = Booking::create([
                'booking_code' => Booking::generateCode(),
                'user_id' => $i % 3 === 0 ? $this->users[array_rand($this->users)]->id : null,
                'movie_id' => $showtime->movie_id,
                'cinema_id' => $showtime->cinema_id,
                'hall_id' => $showtime->hall_id,
                'showtime_id' => $showtime->id,
                'show_date' => $showtime->show_date->toDateString(),
                'start_time' => $showtime->start_time,
                'movie_title' => $showtime->movie->title,
                'cinema_name' => $showtime->cinema->name,
                'hall_name' => $hall->name,
                'format' => $showtime->format,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'seat_count' => count($lines),
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'offer_code' => $i % 9 === 0 ? 'WELCOME10' : null,
                'payment_method' => $method,
                'status' => $status,
                'is_paid' => $isPaid,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($lines as [$seat, $price]) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seat->id,
                    'seat_label' => $seat->row_label.$seat->seat_number,
                    'seat_type' => $seat->seat_type,
                    'price' => $price,
                ]);
            }

            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'provider' => $method,
                'reference' => Payment::generateReference(),
                'amount' => $subtotal,
                'currency' => 'NPR',
                'status' => match (true) {
                    $status === Booking::STATUS_PENDING => Payment::STATUS_PENDING,
                    $status === Booking::STATUS_CANCELLED => Payment::STATUS_FAILED,
                    default => $isPaid ? Payment::STATUS_SUCCESS : Payment::STATUS_PENDING,
                },
                'provider_payload' => [
                    'seeded' => true,
                    'result' => $status === Booking::STATUS_CANCELLED ? 'CANCELLED' : 'OK',
                ],
                'verified_at' => $isPaid ? $createdAt->copy()->addMinutes(3) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
