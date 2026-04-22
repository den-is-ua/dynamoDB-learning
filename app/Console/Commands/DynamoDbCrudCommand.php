<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\DynamoDBService;


#[Signature('ddb:test')]
#[Description('Command description')]
class DynamoDbCrudCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = microtime(true);
        $ddb = new DynamoDBService(DynamoDBService::USER_EVENTS_TABLE);
        
        // $result = $ddb->getUserEventsByUserId(1);
        // $result = $ddb->scanUserEventsByUserId(2635);
        $result = $ddb->getLatestUserEvents(10);    
        
        $end = microtime(true);
        $time = $end - $start;
        
        dump($result);
        $this->info('Time taken: ' . $time . ' seconds');
    }
}
