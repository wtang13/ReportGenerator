<?php

require ('../app/vendor/autoload.php');
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class DBOperations{
    /***Variables*****************/
    private $userid;
    private $log;
    private $sdk;
    /****** Main Functions********/
    public function __construct($args) 
    {
        $count = count($args);
        switch ($count) {
            case 1:
                self::__construct1($args[0]);
                break;
            case 2:
                self::__construct2($args[0],$args[1]);
                break;
        }
    }
    
    private function __construct1($userName)
    {
        $this->userid = $userName;
        $this->log = '';
        date_default_timezone_set('UTC');
        $this->sdk = new Aws\Sdk([
                    'endpoint'   => 'http://localhost:8000',
                    'region'   => 'us-west-2',
                    'version'  => 'latest'
        ]);
    }
    
    private function __construct2($userid, $log)
    {
        $this->userid = $userid;
        $this->log = $log;
        date_default_timezone_set('UTC');
        $this->sdk = new Aws\Sdk([
                    'endpoint'   => 'http://localhost:8000',
                    'region'   => 'us-west-2',
                    'version'  => 'latest'
        ]);
    }
    
    
    /*
     * function:get a specific flight log based in userid and date
     * Output:an array of array: log
     */
    function getFlightLog()
    {
        $dynamodb = $this->sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $tableName = 'Users';
        $json = json_encode([
                ':uid' => $this->userid
            ]);
        $eav = $marshaler->marshalJson($json);
        $params = [
            'TableName' => $tableName,
            'KeyConditionExpression' =>
            '#userid = :uid',
            'ExpressionAttributeNames'=> [ '#userid' => 'userName'],
            'ExpressionAttributeValues'=> $eav
        ];
        $loginfo = [];
        try {
            $result = $dynamodb->query($params);
            foreach ($result['Items'] as $i) {
                $log = $marshaler->unmarshalItem($i);
                foreach($log['Logs'] as $l ) {
                    if ($l['date'] == $this->log){
                        $loginfo['weather'] = $l['weather'];
                        $loginfo['picture'] = $l['picture'];
                        break;
                    }
                }
                break;
            }
        } catch (DynamoDbException $e) {
            echo "Unable to get item:\n";
            echo $e->getMessage() . "\n";
        }
        return $loginfo;
        
    }
    
    /*
     * function : get all previouse flight history
     * out put: array of dates
     */
    function getAllLogs()
    {
        $dynamodb = $this->sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $tableName = 'Users';
        $json = json_encode([
                ':uid' => $this->userid
            ]);
        $eav = $marshaler->marshalJson($json);
        $params = [
            'TableName' => $tableName,
            'KeyConditionExpression' =>
            '#userid = :uid',
            'ExpressionAttributeNames'=> [ '#userid' => 'userName'],
            'ExpressionAttributeValues'=> $eav
        ];
        $logs = [];
        try {
            $result = $dynamodb->query($params);
            foreach ($result['Items'] as $i) {
                $log = $marshaler->unmarshalItem($i);
                foreach($log['Logs'] as $l ) {
                    $logs[] = $l['date'];
                }
            }
        } catch (DynamoDbException $e) {
            echo "Unable to get item:\n";
            echo $e->getMessage() . "\n";
        }
        return $logs;
    }
    
    /*
     * Function: query to find all company information
     * output: array company info for this user id
     */
    function getCompanyInfo()
    {
        $dynamodb = $this->sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $tableName = 'Users';
        $json = json_encode([
                ':uid' => $this->userid
            ]);
        $eav = $marshaler->marshalJson($json);
        $params = [
            'TableName' => $tableName,
            'KeyConditionExpression' =>
            '#na = :uid',
            'ExpressionAttributeNames'=> [ '#na' => 'userName' ],
            'ExpressionAttributeValues'=> $eav
        ];
        $companyInfo = [];
        try {
            $result = $dynamodb->query($params);
            foreach ($result['Items'] as $i) {
                $info = $marshaler->unmarshalItem($i);
                $companyInfo[] = $info['CompanyInfo'];
                
            }
        } catch (DynamoDbException $e) {
            echo "Unable to get item:\n";
            echo $e->getMessage() . "\n";
        }
        return reset($companyInfo);// a user name may work for multiple company, in this environment, default the relation is 1 to 1
    }
    
    
}

