<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ddb:test')]
#[Description('Command description')]
class DynamoDbCrudCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ddb = new \App\Services\DynamoDBService('products');
        
//        $result = $ddb->put([
//            'id' => 2,
//            'sort' => 2,
//            'name' => 'Denys',
//            'email' => 'test@test.com'
//        ]);
        
        dd($ddb->get(['id' => 2, 'sort' => 2]));
    }
}
