<?php
require('MyPDF.php');
class ReporterGenerator{
    /**********Variables************/
    public $pdf;
    private $userid;
    private $userType;
    private $log;
    /********** Main Function Part:constructor********/
    /*
     * Input: Array: $_GET[];
     * function : init
     */
    function __construct($GetArray,$companyName) 
    {
        $this->pdf = new MyPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false,$companyName);
        $this->userid = $GetArray['username'];
        $this->userType = $GetArray['type'];
        $this->log = $GetArray['target'];
    }
    
    /*
     * function: destory this object
     */
    function __destruct() 
    {
        $this->pdf->__destruct();
    }
    /********** Main Function Part:generate pdf********/
    /*
     * Input:String: company name 
     * Function: generate header and footer for each page
     * Output: void
     */
    function getHeaderAndFooter()
    {
        
        //set document information
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('Wenting Tang');
        $this->pdf->SetTitle('Inspection Report');
        // set default header data
        $this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


        // set font
        $this->pdf->SetFont('times', 'BI', 12);

        // add a page
        $this->pdf->AddPage();


        // print a block of text using Write()
        $this->pdf->Write(0, "", '', 0, 'C', true, 0, false, false, 0);

    }
                
    
    /*
     * Input:
     * Array: companyInfo, Int: total Failure, 
     * String: tips from user
     * Function: Add a table contains overall information
     * Return :void
     */
    function getGeneralInfo($companyInfo, $totalFailure, $Tips) 
    {   
        //Add report title
        $s1 ='<h1>Inspection Report<h1>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        
        //Add General information
        $keys = ['name','line1','line2','City','State','Country','Zipcode','Total Failure','Tips'];
        $values = $companyInfo;
        $values['name'] = $this->pdf->companyName;
        $values['Tips'] = $Tips;
        $values['Total Failure'] = $totalFailure;
        $html = $this->pdf->getTableHTML($keys, $values,'General information');
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->nextFewLine(2);
        $this->addPageBreak();
        
    }
    
    /*
     * Input:
     * String: index map's file path
     * Function:add title and picture in report
     * Return: void 
     */
    function getIndexMap($indexMap) {
        //Add index Map
        $s3 = '<h2>Index map of detcted failures</h2>';
        $this->addMapPicture($s3, $indexMap);
        $this->addPageBreak();
    }
    /*
     * Input: $errors: all errors get from database
     * function: generate a table based on dataBase data
     */
    function getFailureSummary($errors)
    {        
        //Add table
        $s2='<h2>Failure Summary</h2>';
        $this->pdf->writeHTML($s2);
        $this->nextFewLine(1);
        
        // column titles
        $header = array('Panel Label', 'Issue');
        $this->pdf->ColoredSummaryTable($header, $errors);
        // add page break
        $this->addPageBreak();
    }
    
    /*
     * Input: array : comnayinfo and weatherInfo
     * function : generate a table based on input array
     */
    function getEnvironmentSummary($companyArray, $weatherInfo)
    {
        $s1='<h2>Environment Summary</h2>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        
        //Add table for weather and company information
        $this->pdf->getEnvironmentTable($companyArray['solarFactoryInfo'], $weatherInfo);
        $this->addPageBreak();
    }
    
    /*
     * Input: an array contains filepath to the picture
     * function: add the picture
     */
    function getPicture($pictureFilePath,$title)
    {
        //Add 2 picture in a map
        $s2 = '<h2>'.$title.'</h2>';
        $this->addMapPicture($s2, $pictureFilePath);
        //Put picture in next Page
        $this->addPageBreak();
    }
    
    /*
     *input: Array[]: data from DB
     *fuction:for a table based on input array
     */
    function getDetaileFailureReport($error)
    {
        $s1='<h2>Detailed failure report</h2>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        for($i = 0; $i < count($error);$i++){
            $this->pdf->addEachCell($error[$i], $i);
            $this->nextFewLine(2);
            if ($i % 2 == 1 && $i != count($error) - 1) {
                $this->addPageBreak();
            }
        }
    }
    
    /*
     * Input: $standard is an array
     * function : 
     * 1. add pie chart based on .csv file
     * 2. add form to calculate error rate
     * 3. add conclusion
     */
    function getAnalysis($rows,$totalerror,$Idel)
    {
       $s = '<h2>Analysis</h2>';
       $this->pdf->writeHTML($s, true, false, true, false, '');
       $this->nextFewLine(2);
       // add error rate chart
       $s1 = '<h2>Inspection Result</h2>';
       $this->pdf->writeHTML($s1, true, false, true, false, '');
       $this->nextFewLine(2);
       $total = $rows;
       $errors = $totalerror;
       $errorRate = ($errors * 100 + 0.0)/ $total;
       $this->pdf->addInspectionTable($errors,$total,$errorRate);
       
       $this->pdf->lastPage();
       $this->pdf->AddPage();
       // Add a pie chart
       $part = $this->getPart($Idel);
       $this->addPieChart($part);
       
       //MayBe add a paragraph Here
    }
    
    /*
     * function : add terms table cell in current pdf
     */
    function getTermsAndDefinition()
    {
        $s = '<h2>Terms And Definition</h2>';
        $this->pdf->writeHTML($s, true, false, true, false, '');
        $this->nextFewLine(2);
        $html = '<table border="1" cellspacing="3" cellpadding="4">
                        <tr>
                            <th>Term</th>
                            <th>Definition</th>
                        </tr>
                        <tr>
                            <td>Mode Index expression</td>
                            <td>Based on user company rule. E.G.: CB-1.1-2.3. CB: combiner box, 1.1:  Area 1 row 1, 2.3: Third of second line</td>
                        </tr>
                        <tr>
                            <td>Hot spot</td>
                            <td>A module(s) identified to be warmer than others suggests the module is disconnected from the system (i.e., open circuited)</td>
                        </tr>
                        <tr>
                            <td>Defective Bypass Diode</td>
                            <td>Randomly heated cell patterns can suggest all bypass diodes have short circuited or that a module is incorrectly connected</td>
                        </tr>
                        <tr>
                            <td>Cracked</td>
                            <td>A module(s) identified obviously broken</td>
                        </tr>
                        <tr>
                            <td>Connector of Fuse Issue</td>
                            <td>Cells are identified to be cooler much more than others suggest the problems in Connector of Fuse Issue</td>
                        </tr>
                    </table>';
        $this->pdf->writeHTML($html, true, false, true, false, '');
        $this->nextFewLine(2);
        
    }
    
    /*
     * May need optimazed in future
     * input: null
     * function: finish write document, save in local project 
     * output: null
     */
    function reportFinish()
    {
        $filepath = '/result/';
        $time = explode(' ', $this->log);
        $filename ='InspectionReport';
        foreach($time as $t) {
            $filename .= $t;
        }
        $filename .= '.pdf';
        
        $this->pdf->output(__DIR__ .$filepath.$filename, 'F');
        return $filename;
    }
    /********Helper Function part **********/
    
    function addPageBreak() {
        $this->pdf->lastPage();
        $this->pdf->AddPage();
    }
    /*GetParts*/
    function getPart($error){
        $dic = [];
        // init dic
        foreach($error as $row) {
            if(!array_key_exists($row['Issue'], $dic)) {
                $dic[$row['Issue']] = 1;
            } else {
               $dic[$row['Issue']] = $dic[$row['Issue']] + 1;
            }
        }
        
        $total = count($error);
        while($i = current($dic)) {
            $key = key($dic);
            $dic[$key] = ($i * 360) / $total;
            next($dic);
        }
        reset($dic);
        return $dic;
    }
    
    /*Get number of inspected modes*/
    function getTotal($fileName)
    {
        $row = 0;
        $total = 0;
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            while (($data = fgetcsv($handle,0,",")) !== FALSE) {
                if ($row == 0) {
                    $row++;
                } else {
                    if ($data[3] > 6.5) {// failed to delete empty row
                        $total ++;
                    }
                }
            }
            fclose($handle);
        }
        return $total;
    }
    
    /*
     * Function: Next line
     */
    function nextFewLine($n)
    {
        $y = $this->pdf->getY();
        $this->pdf->SetY($y + $n*3);
    }
    
    
    /*
     * Function: add a picture
     */
    function addMapPicture($title,$filePath) {
        $this->pdf->writeHTML($title);
        $this->nextFewLine(1);
        $this->pdf->setJPEGQuality(75);
        $y= $this->pdf->GetY();
        $this->pdf->Image($filePath, 10, $y + 10, 150, 70, 'PNG', '', 'N', true, 150,'', false, false, 0, false, false, false);
    }
    
    /*Function: draw a pie chart based on $part
     * $part = array(array(<'name','ErrorName'>,<'area','degreee in Ciecle'>))
     */
    function addPieChart($part)
    {
        $this->pdf->SetFont('helvetica', 'B', 20);
        $xc = 105;
        $yc = 100;
        $r = 50;
        $BC = 255;
        $start = 20;
        $count = count($part);
        $i = 1;
    
        // draw pie chart
        while($area = current($part)){
            $this->pdf->SetFillColor(0, 0, $BC);
            $x = $this->pdf->GetX();
            $y = $this->pdf->GetY();
            if($i == $count){
                $this->pdf->PieSector($xc, $yc, $r, $start, 20, 'FD', false, 0, 2);
            } else {
                $this->pdf->PieSector($xc, $yc, $r, $start, 20 + $area, 'FD', false, 0, 2);
            }
            $this->pdf->SetTextColor(0,0,0);
            $this->pdf->Text($x, $y, key($part));
            $start = $area;
            $BC = $BC - 20;
            next($part);
        }

    }
}

