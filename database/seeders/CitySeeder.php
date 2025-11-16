<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citiesData = [
            'inside_dhaka' => [
                'Dhaka',
                'Dhanmondi',
                'Mirpur',
                'Mohammadpur',
                'Uttara',
                'Banani',
                'Gulshan',
                'Badda',
                'Rampura',
                'Tejgaon',
                'Motijheel',
                'Shyamoli',
                'Farmgate',
                'Malibagh',
                'Moghbazar',
                'Wari',
                'Jatrabari',
                'Kamrangirchar',
                'Lalbagh',
                'Banasree',
                'Khilgaon',
                'Kallyanpur',
                'Shahbagh',
                'Mohakhali',
                'Paltan',
                'Dhalpur',
                'Green Road'
            ],
            'outside_dhaka' => [
                'Gazipur',
                'Narsingdi',
                'Narayanganj',
                'Tangail',
                'Kishoreganj',
                'Manikganj',
                'Munshiganj',
                'Rajbari',
                'Faridpur',
                'Gopalganj',
                'Madaripur',
                'Shariatpur',
                'Chattogram',
                'Cox\'s Bazar',
                'Rangamati',
                'Khagrachhari',
                'Bandarban',
                'Feni',
                'Noakhali',
                'Lakshmipur',
                'Comilla',
                'Chandpur',
                'Brahmanbaria',
                'Rajshahi',
                'Naogaon',
                'Natore',
                'Chapainawabganj',
                'Pabna',
                'Sirajganj',
                'Joypurhat',
                'Bogura',
                'Khulna',
                'Bagerhat',
                'Satkhira',
                'Jessore',
                'Jhenaidah',
                'Magura',
                'Narail',
                'Kushtia',
                'Chuadanga',
                'Meherpur',
                'Barishal',
                'Bhola',
                'Patuakhali',
                'Barguna',
                'Jhalokathi',
                'Pirojpur',
                'Sylhet',
                'Moulvibazar',
                'Habiganj',
                'Sunamganj',
                'Rangpur',
                'Nilphamari',
                'Lalmonirhat',
                'Kurigram',
                'Gaibandha',
                'Dinajpur',
                'Thakurgaon',
                'Panchagarh',
                'Mymensingh',
                'Jamalpur',
                'Sherpur',
                'Netrokona'
            ]
        ];

        $sortOrder = 1;

        // Add inside Dhaka cities
        foreach ($citiesData['inside_dhaka'] as $cityName) {
            City::firstOrCreate(
                ['name' => $cityName, 'country' => 'Bangladesh'],
                [
                    'name' => $cityName,
                    'country' => 'Bangladesh',
                    'country_code' => 'BD',
                    'state' => 'Dhaka',
                    'is_active' => true,
                    'sort_order' => $sortOrder++
                ]
            );
        }

        // Add outside Dhaka cities
        foreach ($citiesData['outside_dhaka'] as $cityName) {
            // Determine state based on city name
            $state = $this->getStateForCity($cityName);
            
            City::firstOrCreate(
                ['name' => $cityName, 'country' => 'Bangladesh'],
                [
                    'name' => $cityName,
                    'country' => 'Bangladesh',
                    'country_code' => 'BD',
                    'state' => $state,
                    'is_active' => true,
                    'sort_order' => $sortOrder++
                ]
            );
        }

        $this->command->info('Total cities seeded: ' . ($sortOrder - 1));
        $this->command->info('Cities seeded successfully!');
    }

    /**
     * Get state name for a city
     */
    private function getStateForCity($cityName): string
    {
        $stateMap = [
            'Gazipur' => 'Outside Dhaka',
            'Narsingdi' => 'Outside Dhaka',
            'Narayanganj' => 'Outside Dhaka',
            'Tangail' => 'Outside Dhaka',
            'Kishoreganj' => 'Outside Dhaka',
            'Manikganj' => 'Outside Dhaka',
            'Munshiganj' => 'Outside Dhaka',
            'Rajbari' => 'Outside Dhaka',
            'Faridpur' => 'Outside Dhaka',
            'Gopalganj' => 'Outside Dhaka',
            'Madaripur' => 'Outside Dhaka',
            'Shariatpur' => 'Outside Dhaka',
            'Chattogram' => 'Chattogram',
            'Cox\'s Bazar' => 'Chattogram',
            'Rangamati' => 'Chattogram',
            'Khagrachhari' => 'Chattogram',
            'Bandarban' => 'Chattogram',
            'Feni' => 'Chattogram',
            'Noakhali' => 'Chattogram',
            'Lakshmipur' => 'Chattogram',
            'Comilla' => 'Chattogram',
            'Chandpur' => 'Chattogram',
            'Brahmanbaria' => 'Chattogram',
            'Rajshahi' => 'Rajshahi',
            'Naogaon' => 'Rajshahi',
            'Natore' => 'Rajshahi',
            'Chapainawabganj' => 'Rajshahi',
            'Pabna' => 'Rajshahi',
            'Sirajganj' => 'Rajshahi',
            'Joypurhat' => 'Rajshahi',
            'Bogura' => 'Rajshahi',
            'Khulna' => 'Khulna',
            'Bagerhat' => 'Khulna',
            'Satkhira' => 'Khulna',
            'Jessore' => 'Khulna',
            'Jhenaidah' => 'Khulna',
            'Magura' => 'Khulna',
            'Narail' => 'Khulna',
            'Kushtia' => 'Khulna',
            'Chuadanga' => 'Khulna',
            'Meherpur' => 'Khulna',
            'Barishal' => 'Barishal',
            'Bhola' => 'Barishal',
            'Patuakhali' => 'Barishal',
            'Barguna' => 'Barishal',
            'Jhalokathi' => 'Barishal',
            'Pirojpur' => 'Barishal',
            'Sylhet' => 'Sylhet',
            'Moulvibazar' => 'Sylhet',
            'Habiganj' => 'Sylhet',
            'Sunamganj' => 'Sylhet',
            'Rangpur' => 'Rangpur',
            'Nilphamari' => 'Rangpur',
            'Lalmonirhat' => 'Rangpur',
            'Kurigram' => 'Rangpur',
            'Gaibandha' => 'Rangpur',
            'Dinajpur' => 'Rangpur',
            'Thakurgaon' => 'Rangpur',
            'Panchagarh' => 'Rangpur',
            'Mymensingh' => 'Mymensingh',
            'Jamalpur' => 'Mymensingh',
            'Sherpur' => 'Mymensingh',
            'Netrokona' => 'Mymensingh',
        ];

        return $stateMap[$cityName] ?? 'Bangladesh';
    }
}
