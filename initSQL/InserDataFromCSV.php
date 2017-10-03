<?php
require('../app/helper/mysqlDBOperations.php');
function doInsert($mysqldb,$tag,$data,$db) {
    try {
            $sql = $mysqldb->parseXmlElement($tag);
            $stmt = $db->prepare($sql);
            $i = 1;
             foreach($data as $value){
                $type = array_keys($value);
                switch($type[0]) {
                    case 'general':
                        $stmt->bindParam($i++, $value[$type[0]]);
                        break;
                    case 'LOB':
                        $mysqldb->doLOBBind($i++,$value[$type[0]],$stmt);
                        break;
                }
            }

            $db->beginTransaction();
            $stmt->execute();
            $db->commit();
            $sql = null;
        } catch (PDOException $e) {
            echo $e->getMessage()."\n";
        }
}
/*
 * Assumption: the data used to do query is in same order as xml file setting
 * Function: Load .csv file and insert data in db for each line
 * Input: $filename: .csv file name $dbObject: an db instance created by xmlfilepath
 * $data: an array store extra data needed to do insert data : each element is 
 * an array with tag and value $tag: xml tag
 */
function loadAndInsert($filename,$dbObject,$extraData,$tag)
{
    $db = $dbObject->getConnection();
    $row = 1;
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 7000, ",")) !== FALSE) {
            if ($row == 1) {
                $row++;
            } else {
                $input = [];
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    $temp = [];
                    $temp['general'] = $data[$c];
                    $input[] = $temp;
                }
                //add extra data
                foreach ($extraData as $temp) {
                    $input[] = $temp;
                }
                // insert in DB
                doInsert($dbObject, $tag, $input,$db);
            }
        }
    }
    $db = null;
    fclose($handle);
}
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

//read .csv file and store it in DB
$detailedErrorFile = '../files/2017-09-08_12-17-35_report.csv';
$standardFile = '../files/2017-08-17_12-17-35_Standard.csv';
$xmlfilepath = '../files/generateReportConfiger.xml';
$db = new mysqlDBOperations($xmlfilepath);
// insert in DroneFlightLog
$droneExtraData = [
    ['general' =>'1992-06-17 08:15:23'] 
];
loadAndInsert($standardFile, $db, $droneExtraData, '//DroneFlightLogData');
/* insert in DetailedError
$toDetailedErrorData = [
    ['LOB' =>'../pictures/Temp-DetailError.png'],
    ['general' =>'1992-06-17 08:15:23']  
];

loadAndInsert($detailedErrorFile, $db, $toDetailedErrorData,'//DetailedErrorData');