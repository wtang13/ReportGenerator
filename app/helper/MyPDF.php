<?php
require ('../TCPDF-master/tcpdf.php');
class MyPDF extends TCPDF{
    /*********variables***********/
    private $companyName;
    public $data;
    /******Override functions************/
    /*
     *Function: init
     */
    public function __construct($orientation , $unit , $format,
            $unicode, $encoding , $diskcache,$companyName,$reportFile) 
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, false);
        $this->companyName = $companyName;
        $this->data = $this->LoadData($reportFile);
    }
    /*
     * Format: 1 cell in the right top corner, first line is companyName, 
     * second line is today's date
     */
    public function Header() 
    {
        // Change based on user time zone
        // Logo
        $image_file = '../pictures/Aerospec.jpg';
        $this->Image($image_file, 10, 10, 45, 15, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        //date_default_timezone_set('Australia/Melbourne')
        $date = date('m/d/Y ', time());
        // Set font
        $this->SetFont('helvetica', '', 20);
        // Title
        $header="";
        if (strlen($this->companyName) > 21){
            $header = $date;
        } else {
            $header = $this->companyName.' '.$date; 
        }
         $this->Cell(0, 15, $header , 0, false, 'R', 0, '', 0, false, 'M', 'M');
    }
    
    /*
     * Format : aero logo + contact info + pagenumber
     */
    public function Footer() 
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 12);
        // Page number
        $this->Cell(0, 10,$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
  
    /*
     * Function: Load table data from file(.csv)
     * File may download from DB or raw data ganerate from DB
     * return 2D array
     */
    public function LoadData($file) 
    {
        $row = 0;
        $result = array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle,0,",")) !== FALSE) {
                if ($row == 0) {
                    $row++;
                } else {
                    if ($this->noFatelEmpty($data)) {// failed to delete empty row
                        $result[] = $data;
                    }
                }
            }
            fclose($handle);
        }
        return $result;
    }
    
    /*
     * $header: array of header of the table
     * $data: data read from .csv
     * $option: summary error report or detailed summary report
     * function: Format summary
     */
    public function ColoredSummaryTable($header,$data) 
    {
        // Colors, line width and bold font
        $this->tabelSetting();
        $this->SetFont('', 'B');
        // Header
        $w = array(50,100);
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        $this->SetFont('');
        // Data
        $fill = 0;
        foreach($data as $row) {
            // only 2: 0 and 4 is needed
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row[4], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill=!$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
    
    
    
    /*
     * $company Array: all company info need to form an area info
     * $weatherInfo: all weather info need to form an weather info
     */
    public function getEnvironmentTable($companyArray, $weatherInfo) 
    {
        // Add weather first
        $this->getDic($weatherInfo, 'Weather Information');
        $this->getDic($companyArray, 'Inspection Area Information');
    }
    
        /*
     * function: add a cell about error's detailed information
     * Imagine at left (40) and a table at right
     */
    function addEachCell($pic,$num)
    {
        $header = array('Panel Label','Latitude','Longitude','Video Time','Issue','Affected Nodes','Index Map Picture');
        $map = '';
        if (count($pic) < 2) {
            $map = $pic[0];
        } else {
            $map = $pic[$num];
        }
        $this->tabelSetting();
        $fill = 0;
        for ($j = 0; $j < 2; $j++) {
            // add First Row
            $this->SetFont('', 'B');
            for($i = 0; $i < 3; $i++) {
                $index = $j * 3 + $i;
                $this->Cell(50, 7, $header[$index], 1, 0, 'C', $fill);
            }
            $this->Ln();
            $this->SetFont('');
            for($i = 0; $i < 3; $i++) {
                $index = $j * 3 + $i;
                if ($index < 5) {
                    $this->Cell(50, 7, $this->data[$num][$index], 1, 0, 'C', $fill);
                } else {
                    $this->Cell(50, 7, 'Add IN Future', 1, 0, 'L', $fill);
                }
            }
            $fill=!$fill;
            $this->Ln();
        }
        $this->SetFont('', 'B');
        $this->Cell(150, 7, $header[6], 1, 0, 'C', 1);
        $this->Ln();
        $y= $this->GetY();
        $x = $this->GetX();
        $this->Image($map, $x, $y+1, 150, 60, 'PNG', '', 'N', true, 150,'', false, false, 0, false, false, false);
        
        
    }
    
    
    /*Fotm a dictionary like inspection result*/
    public function addInspectionTable($errors,$total,$errorRate)
    {
        $this->tabelSetting();
        $this->SetFont('');
        $this->addALineInCell('Errors', ''.$errors);
        $this->addALineInCell('Inspected Modes', ''.$total);
        $this->addALineInCell('Error Rate', ''.$errorRate);
        
        
    }
    
    /*********Helper Function*************************/
    /*Add a line in cell , dictionary like*/
    public function addALineInCell($s1,$s2)
    {
        $this->Cell(40, 6, $s1, 'LR', 0, 'C', 0);
        $this->Cell(60, 6, $s2, 'LR', 0, 'C', 0);
        $this->Ln();
    }
    /*TableSetting*/
    public function tabelSetting()
    {
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetLineWidth(0.3);
    }
    /*Check wether the input tuple have empty element*/
    public function noFatelEmpty($data)
    {
        for($i = 0; $i < count($data); $i++) {
            if ($i == 0 && $data[$i] == ''){
                return false;
            }
        }
        return true;
    }

    /*Form a dictionary like table first column is header and second column is value*/
    public function getDic($pairs,$title) 
    {
        // Colors, line width and bold font
        $this->tabelSetting();
        $this->SetFont('', 'B');
        // Header
        $w = array(70,120);
        $this->Cell(190, 7, $title, 1, 0, 'C', 1);
        $this->Ln();
        
        $this->SetFont('');
        // Data
        $fill = 0;
        $headers = array_keys($pairs);
        for ($i = 0; $i < count($pairs); $i++) {
            $this->Cell($w[0], 6, $headers[$i], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $pairs[$headers[$i]], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill=!$fill;
        }
    }
    
    
    
    }   


