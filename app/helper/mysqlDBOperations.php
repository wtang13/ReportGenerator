<?php
class mysqlDBOperations{
    private $simplexmlObject;
    //based on xmlFilepath, get database setting
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $socket;
    private $charset;
    
    
    /*
     * Function: init based on xml file data
     * input: $xmlFilePath
     */
    function __construct($xmlFilePath) 
    {
        $this->simplexmlObject = simplexml_load_file($xmlFilePath);
        $this->host = $this->parseXmlElement("//host");
        $this->user = $this->parseXmlElement("//user");
        $this->password = $this->parseXmlElement("//password");
        $this->dbname = $this->parseXmlElement("//dbname");
        $this->socket = $this->parseXmlElement("//unix_socket");
        $this->charset = $this->parseXmlElement("//charset");
    }
    
    /*
     * Function: create tables based on simplexmlobject
     */
    function createTables()
    {
        $sqls = $this->simplexmlObject->xpath("//table");
        foreach($sqls as $sql) {
            try {
                $db = $this->getConnection();
                $db->exec($sql);
                $tablename = $sql->getName();
                print("Created $tablename table.\n");
                $this->closeConnection($db, $sql);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        
    }
    
    /*
     * Assumption: no null inserted, if there are 3 attribute in the row, must insert 3
     * Function: insert Data into Tables
     * input: tag, array:{attribute: value} img of txt will pass a file name
     * There are 2 types of attribute: 'general' represent varchar or int type 
     * can fit in a row; 'LOB' a file path to a .txt file,store in key-value pair, or 
     * a file path to a img 
     */
    function insertData($tag,$data)
    {
        try {
            $db = $this->getConnection();
            $sql = $this->parseXmlElement($tag);
            $stmt = $db->prepare($sql);
            $i = 1;
             foreach($data as $value){
                $type = array_keys($value);
                switch($type[0]) {
                    case 'general':
                        $stmt->bindParam($i++, $value[$type[0]]);
                        break;
                    case 'LOB':
                        $this->doLOBBind($i++,$value[$type[0]],$stmt);
                        break;
                }
            }

            $db->beginTransaction();
            $stmt->execute();
            $db->commit();
            $this->closeConnection($db, $sql);
        } catch (PDOException $e) {
            echo $e->getMessage()."\n";
        }
    }
    
    /*
     * Assumption: 1.all bind parameters is provided in data
     * 2. will not search by large data: TEXT OR BLOB
     * Function: do Query to get required data
     * input:
     * $data:an array contains all required data 0=> data1, 1 => data2
     * $tag: an tag in xml leads to specific query
     * $output: expect variable name(s) in this query
     * output:
     * $result
     */
    function queryData($tag,$cols,$data)
    {
        $columns = $this->getColumns($cols);
        $result = 'Empty';
        try{
            $db = $this->getConnection();
            $sql = $this->parseXmlElement($tag);
            $stmt = $db->prepare($sql);
            // bind parameters
            for($i = 0;$i < count($data);$i++) {
                $stmt->bindParam($i + 1, $data[$i]);
            }
            // bind column
            $i = 1;
            $types = array_keys($columns);
            foreach($columns as $col){
                $type = $types[$i - 1];
                switch($type){
                    case 'general':
                        $stmt->bindColumn($i,$col);
                        break;
                    case 'LOB':
                        $stmt->bindColumn($i,$col,PDO::PARAM_LOB);
                        break;
                }
                $i++;
                
            } 
            // do query
            $db->beginTransaction();
            $stmt->execute();
            $db->commit();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->closeConnection($db, $sql);
        }catch(PDOException $e){
            echo $e->getMessage()."\n";
        }
        return $result;
    }
    
    /*
     * Function: based on userName, check whether user in valid,if invalid, return boolean: false 
     */
    function getUserInfo($userName)
    {
        $data = [$userName];
        $output = $this->queryData("//QuerygetUserInfo", "//columnGetUserInfo", $data);
        $user = [];
        foreach($output as $tuple) {
            $user = $tuple;
            break;
        }
        return $user;
    }
    
    /*
     * Function: query to find all company information(name,address,factoryinfo) based on userName
     * output: array company info for this user id
     */
    function getCompanyInfo($userName)
    {
        $data = [$userName];
        $output = $this->queryData('//QueryGetCompanyInfo','//columnGetCompanyInfo', $data);
        $company = [];
        foreach($output as $tuple) {
            $keys = array_keys($tuple);
            $i = 0;
            foreach ($tuple as $data) {
                if ($keys[$i] == 'solarFactoryInfo') {
                    $array = json_decode($data,true);
                    $company[$keys[$i]] = $array; //= $this->addFactoryInfo($company, $array);
                } else {// parse and save to $weather
                    $company[$keys[$i]] = $data;
                }
                $i++;
            }
        }
        return $company;
    }


    /*
     * Function: get all log's flight time  based on userName
     * input:username 
     */
    function getAllLog($userName)
    {
        $data = [$userName];
        $output = $this->queryData("//QueryGetAllLogs", "//columnGetAllLogs", $data);
        $logs = [];
        foreach($output as $tuple) {
            $logs[] = $tuple['flightTime'];
        }
        return $logs;
    }
    
    /*
     * function:get a specific flight log based in date
     * Output:an array of array: log log['errorPicture'] and log['weather']
     */
    function getFlightLog($flightTime)
    {
        $path = '../pictures/errorPictures/';
        if (!file_exists($path)){
            mkdir($path);
        }
        $name = 'testRead.png';
        $data = [$flightTime];
        $output = $this->queryData('//QueryGetFlightLog','//ColumnGetFlightLog', $data);
        $picture = [];
        $weather = [];
        foreach($output as $tuple) {
            $keys = array_keys($tuple);
            $i = 0;
            $j = 1;
            foreach ($tuple as $data) {
                if($keys[$i] == 'errorPicture') {
                    $file = fopen($path.$j.$name,"w+");
                    $picture[] = $path.$j.$name;
                    fwrite($file, $data);
                    fclose($file);
                    $j++;
                } else {// parse and save to $weather
                    $weather = json_decode($data,true);
                }
                $i++;
            }
        }
        $output = [];
        $output['weather'] = $weather;
        $output['picture'] = $picture;
        return $output;
    }
    /****Helper functions****/
    //function: add factory info to company info
    function addFactoryInfo($companyInfo, $factoruInfo)
    {
        $keys = array_keys($factoruInfo);
        $i = 0;
        foreach($factoruInfo as $info) {
            $companyInfo[$keys[$i]] = $info;
            $i++;
        }
        return $companyInfo;
    }
    //function : add attribute's value in data type TEXT
    function doLOBBind($index,$filepath,$stmt)
    {
        $fp = fopen($filepath, 'rb');
        $stmt->bindParam($index,$fp,PDO::PARAM_LOB);
    }
    //function: transfer a xmlsimple element to string
    function parseXmlElement($tag)
    {   
        $temp = $this->simplexmlObject->xpath($tag);
        $output = (string)$temp[0];
        return $output;
    }
    //function : get connection of DB
    function getConnection()
    {
        $dsn = "mysql:unix_socket=$this->socket;host=$this->host;dbname=$this->dbname;charset=$this->charset";
        $pdo = new PDO($dsn,$this->user,$this->password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    
    //function: close connection
    function closeConnection($pdo,$command)
    {
        $pdo = null;
        $command = null;
    }
    
    //Assumption: odd one is the key and even one is the value
    //function: split a string by using $input to get to array to get columns
    function getColumns($input)
    {
        $temp = explode(" ", trim($this->parseXmlElement($input))); 
        $result = [];
        for($i = 0; $i < count($temp);$i = $i + 2) {
            $result[$temp[$i]] = $temp[$i+1];
        }
        return $result;
    }
    

    
}

