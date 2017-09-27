<?php
require('MyPDF.php');
class ReporterGenerator{
    /**********Variables************/
    public $pdf;
    private $userid;
    private $userType;
    private $log;
    private $Mchoice;
    /********** Main Function Part:constructor********/
    /*
     * Input: Array: $_GET[];
     * function : init
     */
    function __construct($GetArray,$companyName,$reportFile) 
    {
        $this->pdf = new MyPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false,$companyName,$reportFile);
        $this->userid = $GetArray['username'];
        $this->userType = $GetArray['type'];
        $this->log = $GetArray['target'];
        $this->Mchoice = $GetArray['printType'];
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
     * Input:String: index map's file path
     * String: location of inspection area, Int: total Failure, 
     * String: tips from user
     * function: Add a cell contains overall information
     * add indexMap for frontpage
     * add name of indexMap
     * void
     */
    function finishFrontpage($indexMap, $location, $totalFailure, $Tips) 
    {   
        //Add report title
        $s1 ='<h1>Inspection Report<h1>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        
        //Add General information
        $s2 = '<h2>General information</h2>';
        $this->pdf->writeHTML($s2);
        $this->nextFewLine(2);
        $this->pdf->Cell(0, 0, 'Location: '.$location, 1, 1, 'C', 0, '', 0);
        $this->pdf->Cell(0, 0, 'Total Failure: '.$totalFailure, 1, 1, 'C', 0, '', 0);
        $this->pdf->Cell(0, 0, 'Tips: '.$Tips, 1, 1, 'C', 0, '', 0);
        $this->nextFewLine(2);
        
        //Add index Map
        $s3 = '<h2>Index map of detcted failures</h2>';
        $this->addMapPicture($s3, $indexMap);

        //Put picture in next Page
        $this->pdf->lastPage();
        $this->pdf->AddPage();
        
    }
    
    /*
     * 
     * function: 
     * 1. add a paragraph to introduce
     * 2. generate a table based on report .csvfile
     */
    function finishSummaryI()
    {
        //Set S2
        $s1='<h2>Summary</h2>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(1);
        $p1='<p>    In summary, there 2 two parts: failure summary and environment summary. Failure Summary contains all detected failures from selected flight log. Environment Summary contains weather, detailed information (area, landform pictures).</p>';
        $this->pdf->writeHTML($p1);
        $this->nextFewLine(2);
        
        //Add table
        $s2='<h3>Failure Summary</h3>';
        $this->pdf->writeHTML($s2);
        $this->nextFewLine(1);
        
        // column titles
        $header = array('Panel Label', 'Issue');
        $this->pdf->ColoredSummaryTable($header, $this->pdf->data);
        // add page break
        $this->pdf->lastPage();
        $this->pdf->AddPage();
    }
    
    /*
     * Input: String: report .csv file path,
     * String: map file path (map file will contian only 2 image), 
     * array: $weatherInfo
     * array: company information
     * function: 
     * 1. add a paragraph to introduce
     * 2. generate a table based on report .csvfile
     * 3. generate weather from time and standard .csv file from google weather
     * 4. generate company cell in that form based on input array
     * 5. add 2 page from map file
     */
    function finishSummaryALL($mapFile, $companyArray, $weatherInfo)
    {
        //AddSummaryI
        $this->finishSummaryI();
        
        $s1='<h3>Environment Summary</h3>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        
        //Add table for weather and company information
        $this->pdf->getEnvironmentTable($companyArray, $weatherInfo);
        // add page break
        $this->pdf->lastPage();
        $this->pdf->AddPage();
        
        //Add 2 picture in a map
        $s2 = '<h3>Flight coverage map</h3>';
        $this->addMapPicture($s2, $mapFile[0]);
        
        $s3 = '<h3>Detailed coverage map</h3>';
        $this->addMapPicture($s3, $mapFile[1]);
        
        //Put picture in next Page
        $this->pdf->lastPage();
        $this->pdf->AddPage();
    }
    
    /*
     *input: Array[]: modePicture file path(fetch from DB)
     * fuction:based on picArrau, reportFile and page number, add detailed mataince info
     * return: page number
     */
    function finishDetaileReport($picArray)
    {
        $s1='<h2>Detailed failure report</h2>';
        $this->pdf->writeHTML($s1);
        $this->nextFewLine(2);
        for($i = 0; $i < count($this->pdf->data);$i++){
            $this->pdf->addEachCell($picArray, $i);
            $this->nextFewLine(2);
            if ($i % 2 == 1 && $i != count($this->pdf->data) - 1) {
                $this->pdf->lastPage();
                $this->pdf->AddPage();
            }
        }
    }
    
    /*
     * function : 
     * 1. add pie chart based on .csv file
     * 2. add form to calculate error rate
     * 3. add conclusion
     */
    function addAnalysis($standard)
    {
       $s = '<h2>Analysis</h2>';
       $this->pdf->writeHTML($s, true, false, true, false, '');
       $this->nextFewLine(2);
       // add error rate chart
       $s1 = '<h2>Inspection Result</h2>';
       $this->pdf->writeHTML($s1, true, false, true, false, '');
       $this->nextFewLine(2);
       $total = $this->getTotal($standard);
       $errors = count($this->pdf->data);
       $errorRate = ($errors * 100 + 0.0)/ $total;
       $this->pdf->addInspectionTable($errors,$total,$errorRate);
       
       $this->pdf->lastPage();
       $this->pdf->AddPage();
       // Add a pie chart
       $part = $this->getPart();
       $this->addPieChart($part);
       
       //MayBe add a paragraph Here
    }
    
    /*
     * function : add terms table cell in current pdf
     */
    function addTerms()
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
        $filename ='InspectionReport'.'.pdf';
        
        $this->pdf->output(__DIR__ .$filepath.$filename, 'F');
    }
    /********Helper Function part **********/
    
    /*GetParts*/
    function getPart(){
        $dic = [];
        // init dic
        foreach($this->pdf->data as $row) {
            if(!array_key_exists($row[4], $dic)) {
                $dic[$row[4]] = 1;
            } else {
               $dic[$row[4]] = $dic[$row[4]] + 1;
            }
        }
        $total = count($this->pdf->data);
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
        $this->pdf->Image($filePath, 10, $y + 10, 190, 90, 'PNG', '', 'N', true, 150,'', false, false, 0, false, false, false);
        $this->nextFewLine(2);
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

