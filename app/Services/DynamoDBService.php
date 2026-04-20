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

    public function get($id)
    {
        $result = $this->client->getItem([
            'TableName' => $this->table,
            'Key' => [
                'id' => ['N' => (string)$id],
            ],
        ]);

        return isset($result['Item'])
            ? $this->marshaler->unmarshalItem($result['Item'])
            : null;
    }
}
