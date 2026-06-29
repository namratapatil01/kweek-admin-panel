<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDriversSeeder extends Seeder
{
    public function run()
    {
        $serviceTypes = ['cab-service', 'rental-service', 'delivery-service', 'parcel_delivery'];
        $carMakes     = ['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes', 'Hyundai', 'Kia', 'Nissan', 'Volkswagen', 'Chevrolet'];
        $carModels    = ['Camry', 'Civic', 'Mustang', 'X5', 'C-Class', 'Elantra', 'Sportage', 'Altima', 'Passat', 'Malibu'];
        $carColors    = ['White', 'Black', 'Silver', 'Red', 'Blue', 'Grey', 'Gold', 'Green'];
        $vehicleTypes = ['Sedan', 'SUV', 'Hatchback', 'Pickup', 'Van'];
        $countryCodes = ['+1', '+44', '+91', '+61', '+971', '+966'];
        $rideTypes    = ['ride', 'intercity', 'both'];

        $drivers = [
            ['firstName' => 'James',    'lastName' => 'Carter',    'email' => 'james.carter@example.com',    'phone' => '5551001001'],
            ['firstName' => 'Sophia',   'lastName' => 'Williams',  'email' => 'sophia.williams@example.com', 'phone' => '5551001002'],
            ['firstName' => 'Liam',     'lastName' => 'Johnson',   'email' => 'liam.johnson@example.com',    'phone' => '5551001003'],
            ['firstName' => 'Emma',     'lastName' => 'Brown',     'email' => 'emma.brown@example.com',      'phone' => '5551001004'],
            ['firstName' => 'Noah',     'lastName' => 'Davis',     'email' => 'noah.davis@example.com',      'phone' => '5551001005'],
            ['firstName' => 'Olivia',   'lastName' => 'Martinez',  'email' => 'olivia.martinez@example.com', 'phone' => '5551001006'],
            ['firstName' => 'Ethan',    'lastName' => 'Garcia',    'email' => 'ethan.garcia@example.com',    'phone' => '5551001007'],
            ['firstName' => 'Ava',      'lastName' => 'Wilson',    'email' => 'ava.wilson@example.com',      'phone' => '5551001008'],
            ['firstName' => 'Lucas',    'lastName' => 'Anderson',  'email' => 'lucas.anderson@example.com',  'phone' => '5551001009'],
            ['firstName' => 'Mia',      'lastName' => 'Taylor',    'email' => 'mia.taylor@example.com',      'phone' => '5551001010'],
            ['firstName' => 'Mason',    'lastName' => 'Thomas',    'email' => 'mason.thomas@example.com',    'phone' => '5551001011'],
            ['firstName' => 'Isabella', 'lastName' => 'Jackson',   'email' => 'isabella.jackson@example.com','phone' => '5551001012'],
            ['firstName' => 'Logan',    'lastName' => 'White',     'email' => 'logan.white@example.com',     'phone' => '5551001013'],
            ['firstName' => 'Charlotte','lastName' => 'Harris',    'email' => 'charlotte.harris@example.com','phone' => '5551001014'],
            ['firstName' => 'Aiden',    'lastName' => 'Clark',     'email' => 'aiden.clark@example.com',     'phone' => '5551001015'],
            ['firstName' => 'Amelia',   'lastName' => 'Lewis',     'email' => 'amelia.lewis@example.com',    'phone' => '5551001016'],
            ['firstName' => 'Jackson',  'lastName' => 'Robinson',  'email' => 'jackson.robinson@example.com','phone' => '5551001017'],
            ['firstName' => 'Harper',   'lastName' => 'Walker',    'email' => 'harper.walker@example.com',   'phone' => '5551001018'],
            ['firstName' => 'Elijah',   'lastName' => 'Hall',      'email' => 'elijah.hall@example.com',     'phone' => '5551001019'],
            ['firstName' => 'Evelyn',   'lastName' => 'Allen',     'email' => 'evelyn.allen@example.com',    'phone' => '5551001020'],
        ];

        foreach ($drivers as $i => $d) {
            $svcType    = $serviceTypes[$i % count($serviceTypes)];
            $carMake    = $carMakes[$i % count($carMakes)];
            $carModel   = $carModels[$i % count($carModels)];
            $carColor   = $carColors[$i % count($carColors)];
            $vehType    = $vehicleTypes[$i % count($vehicleTypes)];
            $countryCode= $countryCodes[$i % count($countryCodes)];
            $rideType   = $rideTypes[$i % count($rideTypes)];
            $isActive   = ($i % 3 !== 2) ? 1 : 0;   // ~2/3 active
            $isDocVerify= ($i % 4 === 0) ? 1 : 0;   // ~1/4 verified
            $plateNum   = strtoupper(substr($carMake, 0, 2)) . rand(1000, 9999);
            $walletAmt  = round(rand(0, 5000) / 10, 2);
            $completed  = rand(0, 250);

            $carInfo = json_encode([
                'carName'  => $carModel,
                'carMakes' => $carMake,
                'carColor' => $carColor,
            ]);

            $bankDetails = json_encode([
                'bankName'      => ['Chase', 'Wells Fargo', 'HDFC', 'Barclays', 'Emirates NBD'][$i % 5],
                'accountNumber' => '****' . rand(1000, 9999),
                'routingNumber' => rand(100000000, 999999999),
                'accountHolder' => $d['firstName'] . ' ' . $d['lastName'],
            ]);

            // Skip if email already exists
            $exists = DB::table('app_users')->where('email', $d['email'])->exists();
            if ($exists) continue;

            DB::table('app_users')->insert([
                'id'                   => \Illuminate\Support\Str::uuid()->toString(),
                'firstName'            => $d['firstName'],
                'lastName'             => $d['lastName'],
                'email'                => $d['email'],
                'phoneNumber'          => $d['phone'],
                'countryCode'          => $countryCode,
                'role'                 => 'driver',
                'active'               => 1,
                'isActive'             => $isActive,
                'isOwner'              => 0,
                'isDocumentVerify'     => $isDocVerify,
                'serviceType'          => $svcType,
                'carName'              => $carModel,
                'carMakes'             => $carMake,
                'carNumber'            => $plateNum,
                'carColor'             => $carColor,
                'vehicleType'          => $vehType,
                'rideType'             => in_array($svcType, ['cab-service']) ? $rideType : null,
                'wallet_amount'        => $walletAmt,
                'orderCompleted'       => $completed,
                'driverRate'           => number_format(rand(35, 50) / 10, 1),
                'carInfo'              => $carInfo,
                'userBankDetails'      => $bankDetails,
                'latitude'             => round(rand(25000, 55000) / 1000, 6),
                'longitude'            => round(rand(-122000, 55000) / 1000, 6),
                'profilePictureURL'    => null,
                'carPictureURL'        => null,
                'fcmToken'             => null,
                'created_at'           => now()->subDays(rand(1, 180)),
                'updated_at'           => now()->subDays(rand(0, 30)),
            ]);
        }

        $this->command->info('✅ 20 dummy drivers inserted successfully!');
    }
}
