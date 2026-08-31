<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [

            /*
            |--------------------------------------------------------------------------
            | Australia
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'AU',
                'name' => 'Australian Capital Territory',
                'code' => 'ACT',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'New South Wales',
                'code' => 'NSW',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'Northern Territory',
                'code' => 'NT',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'Queensland',
                'code' => 'QLD',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'South Australia',
                'code' => 'SA',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'Tasmania',
                'code' => 'TAS',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'Victoria',
                'code' => 'VIC',
            ],
            [
                'country_iso2' => 'AU',
                'name' => 'Western Australia',
                'code' => 'WA',
            ],

            /*
            |--------------------------------------------------------------------------
            | Bangladesh
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'BD',
                'name' => 'Barisal',
                'code' => 'BAR',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Chittagong',
                'code' => 'CTG',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Dhaka',
                'code' => 'DHA',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Khulna',
                'code' => 'KHL',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Mymensingh',
                'code' => 'MYM',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Rajshahi',
                'code' => 'RAJ',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Rangpur',
                'code' => 'RNG',
            ],
            [
                'country_iso2' => 'BD',
                'name' => 'Sylhet',
                'code' => 'SYL',
            ],

            /*
            |--------------------------------------------------------------------------
            | India
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'IN',
                'name' => 'Andhra Pradesh',
                'code' => 'AP',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Arunachal Pradesh',
                'code' => 'AR',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Assam',
                'code' => 'AS',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Bihar',
                'code' => 'BR',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Chhattisgarh',
                'code' => 'CG',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Goa',
                'code' => 'GA',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Gujarat',
                'code' => 'GJ',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Haryana',
                'code' => 'HR',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Himachal Pradesh',
                'code' => 'HP',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Jharkhand',
                'code' => 'JH',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Karnataka',
                'code' => 'KA',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Kerala',
                'code' => 'KL',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Madhya Pradesh',
                'code' => 'MP',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Maharashtra',
                'code' => 'MH',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Manipur',
                'code' => 'MN',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Meghalaya',
                'code' => 'ML',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Mizoram',
                'code' => 'MZ',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Nagaland',
                'code' => 'NL',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Odisha',
                'code' => 'OD',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Punjab',
                'code' => 'PB',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Rajasthan',
                'code' => 'RJ',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Sikkim',
                'code' => 'SK',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Tamil Nadu',
                'code' => 'TN',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Telangana',
                'code' => 'TG',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Tripura',
                'code' => 'TR',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Uttar Pradesh',
                'code' => 'UP',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Uttarakhand',
                'code' => 'UK',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'West Bengal',
                'code' => 'WB',
            ],

            /*
            |--------------------------------------------------------------------------
            | India - Union Territories
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'IN',
                'name' => 'Andaman and Nicobar Islands',
                'code' => 'AN',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Chandigarh',
                'code' => 'CH',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Dadra and Nagar Haveli and Daman and Diu',
                'code' => 'DH',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Delhi',
                'code' => 'DL',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Jammu and Kashmir',
                'code' => 'JK',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Ladakh',
                'code' => 'LA',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Lakshadweep',
                'code' => 'LD',
            ],
            [
                'country_iso2' => 'IN',
                'name' => 'Puducherry',
                'code' => 'PY',
            ],

            /*
            |--------------------------------------------------------------------------
            | United Arab Emirates
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'AE',
                'name' => 'Abu Dhabi',
                'code' => 'AUH',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Ajman',
                'code' => 'AJM',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Dubai',
                'code' => 'DXB',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Fujairah',
                'code' => 'FUJ',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Ras Al Khaimah',
                'code' => 'RAK',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Sharjah',
                'code' => 'SHJ',
            ],
            [
                'country_iso2' => 'AE',
                'name' => 'Umm Al Quwain',
                'code' => 'UAQ',
            ],

            /*
            |--------------------------------------------------------------------------
            | United Kingdom
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'GB',
                'name' => 'England',
                'code' => 'ENG',
            ],
            [
                'country_iso2' => 'GB',
                'name' => 'Scotland',
                'code' => 'SCT',
            ],
            [
                'country_iso2' => 'GB',
                'name' => 'Wales',
                'code' => 'WLS',
            ],
            [
                'country_iso2' => 'GB',
                'name' => 'Northern Ireland',
                'code' => 'NIR',
            ],

            /*
            |--------------------------------------------------------------------------
            | United States
            |--------------------------------------------------------------------------
            */

            [
                'country_iso2' => 'US',
                'name' => 'Alabama',
                'code' => 'AL',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Alaska',
                'code' => 'AK',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Arizona',
                'code' => 'AZ',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Arkansas',
                'code' => 'AR',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'California',
                'code' => 'CA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Colorado',
                'code' => 'CO',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Connecticut',
                'code' => 'CT',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Delaware',
                'code' => 'DE',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Florida',
                'code' => 'FL',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Georgia',
                'code' => 'GA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Hawaii',
                'code' => 'HI',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Idaho',
                'code' => 'ID',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Illinois',
                'code' => 'IL',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Indiana',
                'code' => 'IN',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Iowa',
                'code' => 'IA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Kansas',
                'code' => 'KS',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Kentucky',
                'code' => 'KY',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Louisiana',
                'code' => 'LA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Maine',
                'code' => 'ME',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Maryland',
                'code' => 'MD',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Massachusetts',
                'code' => 'MA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Michigan',
                'code' => 'MI',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Minnesota',
                'code' => 'MN',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Mississippi',
                'code' => 'MS',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Missouri',
                'code' => 'MO',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Montana',
                'code' => 'MT',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Nebraska',
                'code' => 'NE',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Nevada',
                'code' => 'NV',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'New Hampshire',
                'code' => 'NH',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'New Jersey',
                'code' => 'NJ',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'New Mexico',
                'code' => 'NM',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'New York',
                'code' => 'NY',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'North Carolina',
                'code' => 'NC',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'North Dakota',
                'code' => 'ND',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Ohio',
                'code' => 'OH',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Oklahoma',
                'code' => 'OK',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Oregon',
                'code' => 'OR',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Pennsylvania',
                'code' => 'PA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Rhode Island',
                'code' => 'RI',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'South Carolina',
                'code' => 'SC',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'South Dakota',
                'code' => 'SD',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Tennessee',
                'code' => 'TN',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Texas',
                'code' => 'TX',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Utah',
                'code' => 'UT',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Vermont',
                'code' => 'VT',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Virginia',
                'code' => 'VA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Washington',
                'code' => 'WA',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'West Virginia',
                'code' => 'WV',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Wisconsin',
                'code' => 'WI',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'Wyoming',
                'code' => 'WY',
            ],
            [
                'country_iso2' => 'US',
                'name' => 'District of Columbia',
                'code' => 'DC',
            ],
        ];

        foreach ($regions as $region) {

            $country = Country::where(
                'iso2',
                $region['country_iso2']
            )->first();

            if (! $country) {
                continue;
            }

            Region::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'name' => $region['name'],
                ],
                [
                    'code' => $region['code'],
                    'is_active' => true,
                ]
            );
        }
    }
}
