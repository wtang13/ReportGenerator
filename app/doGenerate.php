<?php
// call DB get necessary data
// Test get data
require('../app/helper/ReportGenerator.php');
require('../app/helper/DBOperations.php');
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

/*END*/
$args = [];
$args[] = $_POST['username'];  
$args[] = $_POST['target'];
$db = new DBOperations($args);
// based on log's data and user name get company info
$companyInfo = $db->getCompanyInfo();
$flightLog = $db->getFlightLog();

//default variables
$indexMap = '../pictures/ErrorInMap.png';
$reportFile = '../files/2017-09-08_12-17-35_report.csv';
$standardFile = '../files/2017-08-17_12-17-35_Standard.csv';
$Tips = 'HAHAHA';
$mapFile = [];
$mapFile[] = '../pictures/CoverageMap.png';
$mapFile[] = '../pictures/DetailCoverage.png';

$job = new ReporterGenerator($_POST, $companyInfo['companyName'], $reportFile);
if ($_POST['type'] == 'worker' || $_POST['printType'] == 'WORKER') {
    $job->getHeaderAndFooter();
    $job->finishFrontpage($indexMap, $companyInfo['Location'], count($job->pdf->data), $Tips);
    $job->finishSummaryI();
    $job->finishDetaileReport($flightLog['picture']);
} else {
    $job->getHeaderAndFooter();
    $job->finishFrontpage($indexMap, $companyInfo['Location'], count($job->pdf->data), $Tips);
    $job->finishSummaryALL($mapFile,$companyInfo,$flightLog['weather']);
    $job->finishDetaileReport($flightLog['picture']);
    $job->addTerms();
    $job->addAnalysis($standardFile);
}
$job->reportFinish();
//direct to pdf
header("Content-type:application/pdf");
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'helper/result/InspectionReport.pdf';
header("Location: http://$host$uri/$extra");
exit;




