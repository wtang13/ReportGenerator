<?php
require '../app/vendor/autoload.php';

date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

$sdk = new Aws\Sdk([
    'endpoint'   => 'http://localhost:8000',
    'region'   => 'us-west-2',
    'version'  => 'latest'
]);

$dynamodb = $sdk->createDynamoDb();
$marshaler = new Marshaler();

$tableName = 'Users';
//SEPRATE DIFFERENT TABLES
 // add flight logs : add 2
$user = [
    'userName' => 'wtang13',
    'Logs'=>[
        ['date' => '2017/08/20 8:00',
            'weather' => [
                'general' => 'Sunny/Cloudy',
                'Temperature' => '23 °C',
                'wind' => '11 mpf',
            ],
            'picture' => ['../pictures/Temp-DetailError.png']
        ],
        ['date' => '2017/06/17 8:00',
        'weather' => [
            'general' => 'Sunny',
            'Temperature' => '23 °C',
            'wind' => '10 mpf',
        ],
        'picture' => ['../pictures/Temp-DetailError.png']
        ]
    ],
    'CompanyInfo'=>[
        'companyName' => 'NAME SHOULD LONG ENOUGH ENERGY SOLUTION',
        'Location'=>'3001 south king drive, apt 215, Chicago, IL,US',
        'MODULE MODEL'=>'CHINA SUNERGY CSUN310-72P',
        'MODULE STC DC RATING' =>'310W',
        'MODULES PER SOURCE CIRCUIT' =>'19',
        'TOTAL MODULE COUNT' => '8,436',
        'TOTAL STC DC SYSTEM SIZE (KW)' => '2615.16',
        'TOTAL AC SYSTEM SIZE (KW)'=>'2000',
        'INVERTER MODEL' =>'(2) SOLECTRIA SGI 750XTM (750kW) AND (1) 500XTM (500kW)',
        'RACKING SYSTEM' => 'GAME CHANGE',
        'MODULE TILT' => '25°',
        'ARRAY AZIMUTH' => '180°',
        'SITE LATITUDE' =>'36°N'
    ]
];
    


        $json = json_encode($user);

        $params = [
            'TableName' => $tableName,
            'Item' => $marshaler->marshalJson($json)
        ];

        try {
            $result = $dynamodb->putItem($params);
            echo "Added log: " . $user['userName'] . "\n";
        } catch (DynamoDbException $e) {
            echo "Unable to add logs:\n";
            echo $e->getMessage() . "\n";
        }
        
    

