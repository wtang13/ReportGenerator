<?php
class mysqlDBOperations{
    private $simplexmlObject;
    //based on xmlFilepath, get database setting
    private $host;
    private $user;
    private $password;
    private $dbname;
    
    
    /*
     * Function: init based on xml file data
     * input: $xmlFilePath
     */
    function __construct($xmlFilePath) 
    {
        $this->simplexmlObject = simplexml_load_file($xmlFilePath);
        $this->host = $this->simplexmlObject->xpath("//host");
        $this->user = $this->simplexmlObject->xpath("//user");
        $this->root = $this->simplexmlObject->xpath("//root");
        $this->dbname = $this->simplexmlObject->xpath("//dbname");
    }
    
    /*
     * Function: create tables based on simplexmlobject
     */
    function createTables()
    {
        $connect = $this->getConnection();
        $sqls = $this->simplexmlObject->xpath("//table");
        foreach($sqls as $sql) {
            if($connect->query($sql) === TRUE) {
                echo "success";
            } else {
                echo "Error:" . $connect->error;
            }
        }
        $connect->close();
    }
    /****Helper functions****/
    //function : get connection of DB
    function getConnection()
    {
        $con = mysqli_connect($this->host, $this->user, $this->password,$this->dbname);
        if (!$con) {
            exit('Connect Error (' . mysqli_connect_errno() . ') '
                   . mysqli_connect_error());
        }
        //set the default client character set 
        mysqli_set_charset($con, 'utf-8');
        return $con;
    }
}

