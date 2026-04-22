<?php

namespace Database\Seeders;

use App\Services\DynamoDBService;
use Illuminate\Database\Seeder;

class UserEventsSeeder extends Seeder
{
    /**
     * Seed the DynamoDB `user_events` table.
     */
    public function run(): void
    {
        $ddb = new DynamoDBService('user_events');

        $baseTs = time();

        for ($i = 0; $i < 1000; $i++) {

            $items = [];
            for ($j = 0; $j < 100; $j++) {
                $items[] = [
                    'user_id' => rand(1, 10000),
                    'timestamp' => $baseTs - rand(0, 1000000),
                    'event_type' => ['view', 'click', 'purchase'][rand(0, 2)],
                    'payload' => json_encode(['page' => ['home', 'pricing', 'checkout'][rand(0, 2)]]),
                ];
            }

            $ddb->putBatch($items); 
        }
    }   
}
