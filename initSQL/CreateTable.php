<?php
/*
 * Function:read create sql from xml by tag<table>, the execute each xml
 */
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

require '../app/helper/mysqlDBOperations.php';
$filepath = '../files/generateReportConfiger.xml';
$operation = new mysqlDBOperations($filepath);
$operation->createTables();