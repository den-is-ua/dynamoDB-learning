<?php
namespace App\Services;


use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;

/**
 * Description of DynamoDBService
 *
 * @author admin
 */
class DynamoDBService 
{
    const USER_EVENTS_TABLE = 'user_events';
    const LATEST_USER_EVENTS_INDEX = 'latest_user_events_index';
    const USER_EVENT_ENTITY = 'USER_EVENT';


    public function __construct(private $table)
    {
        $this->client = self::client();

        $this->marshaler = new Marshaler();
    }

    public static function client(): DynamoDbClient
    {
        return new DynamoDbClient([
            'region' => config('aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('aws.credentials.key'),
                'secret' => config('aws.credentials.secret'),
            ],
            'endpoint' => config('aws.dynamodb_endpoint'),
        ]);
    }

    private function marshaler(): Marshaler
    {
        return $this->marshaler;
    }

    public function put(array $data)
    {
        $item = $this->marshaler->marshalItem($data);

        return $this->client->putItem([
            'TableName' => $this->table,
            'Item' => $item,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function putBatch(array $items)
    {
        $responses = [];

        foreach (array_chunk($items, 25) as $chunk) {
            $requestItems = array_map(function (array $item) {
                return [
                    'PutRequest' => [
                        'Item' => $this->marshaler->marshalItem($item),
                    ],
                ];
            }, $chunk);

            $responses[] = $this->client->batchWriteItem([
                'RequestItems' => [
                    $this->table => $requestItems,
                ],
            ]);
        }

        return $responses;
    }

    /**
     * @param  array<string, mixed>  $key  All primary-key attributes (partition key and sort key if the table has one).
     */
    public function get(array $key)
    {
        $result = $this->client->getItem([
            'TableName' => $this->table,
            'Key' => $this->marshaler->marshalItem($key),
        ]);

        return isset($result['Item'])
            ? $this->marshaler->unmarshalItem($result['Item'])
            : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUserEventsByUserId(int $userId): array
    {
        $result = $this->client->query([
            'TableName' => $this->table,
            'KeyConditionExpression' => 'user_id = :user_id',
            'ExpressionAttributeValues' => [
                ':user_id' => $this->marshaler->marshalValue($userId),
            ],
            'ScanIndexForward' => false,
        ]);

        return array_map(function (array $item) {
            return $this->marshaler->unmarshalItem($item);
        }, $result['Items'] ?? []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function scanUserEventsByUserId(int $userId): array
    {
        $params = [
            'TableName' => $this->table,
            'FilterExpression' => 'user_id = :user_id',
            'ExpressionAttributeValues' => [
                ':user_id' => $this->marshaler->marshalValue($userId),
            ],
        ];

        $result = $this->client->scan($params);

        return array_map(function (array $item) {
            return $this->marshaler->unmarshalItem($item);
        }, $result['Items'] ?? []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLatestUserEvents(int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        $result = $this->client->query([
            'TableName' => $this->table,
            'IndexName' => self::LATEST_USER_EVENTS_INDEX,
            'KeyConditionExpression' => 'entity = :entity',
            'ExpressionAttributeValues' => [
                ':entity' => $this->marshaler->marshalValue(self::USER_EVENT_ENTITY),
            ],
            'ScanIndexForward' => false,
            'Limit' => $limit,
        ]);

        return array_map(function (array $item) {
            return $this->marshaler->unmarshalItem($item);
        }, $result['Items'] ?? []);
    }

}
