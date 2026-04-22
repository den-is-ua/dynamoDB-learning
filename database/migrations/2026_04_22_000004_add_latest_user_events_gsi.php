<?php

use App\Services\DynamoDBService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const TABLE_NAME = 'user_events';
    private const INDEX_NAME = 'latest_user_events_index';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $client = DynamoDBService::client();

        if (! $this->tableExists($client, self::TABLE_NAME)) {
            return;
        }

        if ($this->indexExists($client, self::TABLE_NAME, self::INDEX_NAME)) {
            return;
        }

        $client->updateTable([
            'TableName' => self::TABLE_NAME,
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'entity',
                    'AttributeType' => 'S',
                ],
            ],
            'GlobalSecondaryIndexUpdates' => [
                [
                    'Create' => [
                        'IndexName' => self::INDEX_NAME,
                        'KeySchema' => [
                            [
                                'AttributeName' => 'entity',
                                'KeyType' => 'HASH',
                            ],
                            [
                                'AttributeName' => 'timestamp',
                                'KeyType' => 'RANGE',
                            ],
                        ],
                        'Projection' => [
                            'ProjectionType' => 'ALL',
                        ],
                    ],
                ],
            ],
        ]);

        $client->waitUntil('TableExists', [
            'TableName' => self::TABLE_NAME,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $client = DynamoDBService::client();

        if (! $this->tableExists($client, self::TABLE_NAME)) {
            return;
        }

        if (! $this->indexExists($client, self::TABLE_NAME, self::INDEX_NAME)) {
            return;
        }

        $client->updateTable([
            'TableName' => self::TABLE_NAME,
            'GlobalSecondaryIndexUpdates' => [
                [
                    'Delete' => [
                        'IndexName' => self::INDEX_NAME,
                    ],
                ],
            ],
        ]);

        $client->waitUntil('TableExists', [
            'TableName' => self::TABLE_NAME,
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

    private function indexExists(DynamoDbClient $client, string $tableName, string $indexName): bool
    {
        $result = $client->describeTable(['TableName' => $tableName]);
        $indexes = $result['Table']['GlobalSecondaryIndexes'] ?? [];

        foreach ($indexes as $index) {
            if (($index['IndexName'] ?? null) === $indexName) {
                return true;
            }
        }

        return false;
    }
};
