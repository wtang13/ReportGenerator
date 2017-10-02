<?php
require('../app/helper/mysqlDBOperations.php');
$filepath = '../files/generateReportConfiger.xml';
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
if (!empty($_GET['username'])) {
    $username = $_GET['username'];
    $db = new mysqlDBOperations($filepath);
    $result = $db->getUserInfo($username);
    if (count($result) == 0) {
       $result['userName'] = '';
       $result['userType'] = '';
    } 
    echo json_encode($result);
}
