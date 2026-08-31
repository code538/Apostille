<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [

            [
                'name' => 'Australia',
                'iso2' => 'AU',
                'iso3' => 'AUS',
                'phone_code' => '+61',
                'currency_code' => 'AUD',
                'is_active' => true,
            ],

            [
                'name' => 'Bangladesh',
                'iso2' => 'BD',
                'iso3' => 'BGD',
                'phone_code' => '+880',
                'currency_code' => 'BDT',
                'is_active' => true,
            ],

            [
                'name' => 'India',
                'iso2' => 'IN',
                'iso3' => 'IND',
                'phone_code' => '+91',
                'currency_code' => 'INR',
                'is_active' => true,
            ],

            [
                'name' => 'United Arab Emirates',
                'iso2' => 'AE',
                'iso3' => 'ARE',
                'phone_code' => '+971',
                'currency_code' => 'AED',
                'is_active' => true,
            ],

            [
                'name' => 'United Kingdom',
                'iso2' => 'GB',
                'iso3' => 'GBR',
                'phone_code' => '+44',
                'currency_code' => 'GBP',
                'is_active' => true,
            ],

            [
                'name' => 'United States',
                'iso2' => 'US',
                'iso3' => 'USA',
                'phone_code' => '+1',
                'currency_code' => 'USD',
                'is_active' => true,
            ],

        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                [
                    'iso2' => $country['iso2'],
                ],
                $country
            );
        }
    }
}
