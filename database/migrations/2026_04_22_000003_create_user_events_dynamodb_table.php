<?php

use App\Services\DynamoDBService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $client = DynamoDBService::client();

        if ($this->tableExists($client, 'user_events')) {
            return;
        }

        $client->createTable([
            'TableName' => 'user_events',
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'user_id',
                    'AttributeType' => 'N',
                ],
                [
                    'AttributeName' => 'timestamp',
                    'AttributeType' => 'N',
                ],
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'user_id',
                    'KeyType' => 'HASH',
                ],
                [
                    'AttributeName' => 'timestamp',
                    'KeyType' => 'RANGE',
                ],
            ],
            'BillingMode' => 'PAY_PER_REQUEST',
        ]);

        $client->waitUntil('TableExists', [
            'TableName' => 'user_events',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $client = DynamoDBService::client();

        if (! $this->tableExists($client, 'user_events')) {
            return;
        }

        $client->deleteTable([
            'TableName' => 'user_events',
        ]);

        $client->waitUntil('TableNotExists', [
            'TableName' => 'user_events',
        ]);
    }

    private function tableExists(DynamoDbClient $client, string $tableName): bool
    {
        try {
            $client->describeTable(['TableName' => $tableName]);

            return true;
        } catch (AwsException $exception) {
            if ($exception->getAwsErrorCode() === 'ResourceNotFoundException') {
                return false;
            }

            throw $exception;
        }
    }
};
