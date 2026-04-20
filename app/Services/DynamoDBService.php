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
    public function __construct(private $table)
    {
        $this->client = new DynamoDbClient([
            'region' => config('aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('aws.credentials.key'),
                'secret' => config('aws.credentials.secret'),
            ],
        ]);

        $this->marshaler = new Marshaler();
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
}
