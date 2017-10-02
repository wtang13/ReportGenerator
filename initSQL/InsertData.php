<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require '../app/helper/mysqlDBOperations.php';
$filepath = '../files/generateReportConfiger.xml';
$op = new mysqlDBOperations($filepath);

// insert in User
$User = [
    ['general' =>'wtang13'],
    ['general' =>'Admin'],
    ['general' =>'Wenting'],
    ['general' =>'Tang']
];
$op->insertData('//UserData', $User);

//insert in Company
$Company = [
    ['general' =>'test001'],
    ['general'=>'TestLongEnoughName COMPANY'],
    ['LOB' =>'../files/factoryInfo.json'],
    ['general'=>'wtang13']
];
$op->insertData('//CompanyData', $Company);

//insert in Address
$Address = [
    ['general' =>'3001 South King Dirve'],
    ['general' =>'Apt215'],
    ['general' =>'Chicago'],
    ['general' =>'IL'],
    ['general' =>'United State'],
    ['general' =>'60616'],
    ['general' =>'test001']
];

$op->insertData('//AddressData', $Address);

//insert in Log
$Log = [
    ['general' =>'1992-06-17 08:15:23'],
    ['LOB'=>'../files/weatherInfo.json'],
    ['general'=>'wtang13']
];
$op->insertData('//LogData', $Log);


//insert in ErrorPicture
$error = [
    ['general' =>'CB-1.1-2.3'],
    ['LOB' =>'../pictures/Temp-DetailError.png'],
    ['general' =>'1992-06-17 08:15:23'],
];
$op->insertData('//ErrorPictureData', $error);