<?php
//Dynamo DB : put everything in 1 table
require ('../app/vendor/autoload.php');

date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;

$sdk = new Aws\Sdk([
    'endpoint'   => 'http://localhost:8000',
    'region'   => 'us-west-2',
    'version'  => 'latest'
]);

$dynamodb = $sdk->createDynamoDb();

$user = [
    'TableName' => 'Users',
    'KeySchema' => [
        [
            'AttributeName' => 'userName',
            'KeyType' => 'HASH'  //Partition key
        ]
    ],
    'AttributeDefinitions' => [
        [
            'AttributeName' => 'userName',
            'AttributeType' => 'S'
        ]
    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits' => 10,
        'WriteCapacityUnits' => 10
    ]
];


try {
    $result = $dynamodb->createTable($user);
    echo 'Created table.  Status: ' . 
        $result['TableDescription']['TableStatus'] ."\n";

} catch (DynamoDbException $e) {
    echo "Unable to create table:\n";
    echo $e->getMessage() . "\n";
}







