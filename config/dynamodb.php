<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default DynamoDB table
    |--------------------------------------------------------------------------
    |
    | Logical table name for app code (e.g. DynamoDBService, jobs). Override
    | per call when using multiple tables.
    |
    */

    'default_table' => env('DYNAMODB_TABLE', 'products'),

];
