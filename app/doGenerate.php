<?php



require('../app/helper/ReportGenerator.php');
require('../app/helper/mysqlDBOperations.php');
$filepath = '../files/generateReportConfiger.xml';

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
$db = new mysqlDBOperations($filepath);
$companyInfo = $db->getCompanyInfo($_GET['username']);
$flightLog = $db->getFlightLog($_GET['target']);
$totalFailure =$db->getTotalFaliures();
$rows = $db->getTotalInspectedPanels();
$errors = $db->getIdelPanelInfo($_GET['target']);

//default variables : not default any more : can get from webPage
$indexMap = $flightLog['ErrorInMap'];


$Tips = 'HAHAHA';//update this in Future

$coverageMapFile = $flightLog['CoverageMap'];
$detailedMapFile = $flightLog['DetailedCoverageMap'];

$job = new ReporterGenerator($_GET, $companyInfo['name']);

$job->getHeaderAndFooter();
if ($_GET['generalInfo'] == 'selected') {
    $job->getGeneralInfo($companyInfo, $totalFailure, $Tips);
}

if ($_GET['indexMap'] == 'selected') {
    $job->getIndexMap($indexMap);
}

if ($_GET['failureSummary'] == 'selected') {
    $job->getFailureSummary($errors);
}

if ($_GET['environmentSummary'] == 'selected') {
    $job->getEnvironmentSummary($companyInfo, $flightLog['weather']);
}

if ($_GET['detailedFailureReport'] == 'selected') {
    $job->getDetaileFailureReport($errors);
}

if ($_GET['flightCoverageMap'] == 'selected') {
    $job->getPicture($coverageMapFile, 'Flight Coverage Map');
}

if ($_GET['detailedCoverageMap'] == 'selected') {
    $job->getPicture($detailedMapFile, 'Detailed Coverage Map');
}

if ($_GET['termsAndDefinition'] == 'selected') {
    $job->getTermsAndDefinition();
}

if ($_GET['analysis'] == 'selected') {
    $job->getAnalysis($rows,$totalFailure,$errors);
}

$newFileName = $job->reportFinish();
//direct to pdf
header("Content-type:application/pdf");
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'helper/result/'.$newFileName;
header("Location: http://$host$uri/$extra");
exit;
/*
if ($_POST['indexMap'] == 'selected') {
    $job->getIndexMap($indexMap);
}
if ($_POST['failureSummary'] == 'selected') {
    $job->getFailureSummary($errors);
}

if ($_POST['EnvironmentSummary'] == 'selected') {
    $job->getEnvironmentSummary($companyInfo, $flightLog['weather']);
}

if ($_POST['DetailedFailureReport'] == 'selected') {
    $job->getDetaileFailureReport($errors);
}

if ($_POST['FlightCoverageMap'] == 'selected') {
    $job->getPicture($coverageMapFile, 'Flight Coverage Map');
}

if ($_POST['DetailedCoverageMap'] == 'selected') {
    $job->getPicture($detailedMapFile, 'Detailed Coverage Map');
}

if ($_POST['TermsAndDefinition'] == 'selected') {
    $job->getTermsAndDefinition();
}

if ($_POST['Analysis'] == 'selected') {
    $job->getAnalysis($rows);
}

$newFileName = $job->reportFinish();
//direct to pdf
header("Content-type:application/pdf");
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'helper/result/'.$newFileName;
header("Location: http://$host$uri/$extra");
exit;








