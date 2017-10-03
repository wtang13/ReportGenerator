<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
require '../app/helper/mysqlDBOperations.php';
$filepath = '../files/generateReportConfiger.xml';
$op = new mysqlDBOperations($filepath);

//get all log
$result = $op->getAllLog('wtang13');
var_dump($result);

/* get userinfo
$result = $op->getUserInfo('wtang13');
echo json_encode($result);

/*
$flightTime = '1992-06-17 08:15:23';
//get detailed errors
$result = $op ->getIdelPanelInfo($flightTime);
var_dump($result);

/* get total inspected area
$result = $op->getTotalInspectedPanels();
var_dump($result);
/* get total failures
$result = $op->getTotalFaliures();
var_dump($result);

/*based in flightTime get errorPicture and weatherInfo : PASS
$flightTime = [];
$flightTime[] = '1992-06-17 08:15:23';
$result = $op->getFlightLog($flightTime);
var_dump($result);


/*based in userName, get all flightTime :PASS
$userName = [];
$userName[] = 'wtang13';
$logs = $op->getAllLog($userName);
var_dump($logs);

/*based in userName, get company info : PASS

$userName = [];
$userName[] = 'wtang13';
$companyInfo = $op->getCompanyInfo($userName);
var_dump($companyInfo);
*/