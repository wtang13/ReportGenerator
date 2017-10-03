
<div class ="body">
    <form class ="form" action="../app/doGenerate.php" method="GET">
        <div class ="name">
            <label> User Name: </label>
            <input class="userid" type="text" name="username">
            <label class ="fetchdata">Valid User</label>
        </div>
        
        <div class="type">
            <label> User Type: </label>
            <select class ="option" name="type">
                <option>...</option>
            </select>
        </div>
        
        
        
        <div class ="inspection">
            <label> Inspections: </label>
            <select class="logs" name ="target">
                <option>...</option>
            </select>
        </div>
        
        <div class="printType">
            <p>Please select necessary section to form report</p>
            
            <input type="checkbox" id="GeneralInfo" name ="generalInfo" value='selected'>
            <label for="GeneralInfo">General Information</label>
            
            <input type="checkbox" id="IndexMapOfDetectedFailures" name ="indexMap" value='selected'>
            <label for="GeneralInfo">Index Map Of Detected Failures</label>
            
            <input type="checkbox" id="FailureSummary" name ="failureSummary" value='selected'>
            <label for="GeneralInfo">Failure Summary</label>
            
            <input type="checkbox" id="DetailedFailureReport" name ="detailedFailureReport" value='selected'>
            <label for="GeneralInfo">Detailed Failure Report</label>
            
            <div class ='hide'>
                <input type="checkbox" id="EnvironmentSummary" name ="environmentSummary" value='selected'>
                <label for="GeneralInfo">Environment Summary</label>

                <input type="checkbox" id="FlightCoverageMap" name ="flightCoverageMap" value='selected'>
                <label for="GeneralInfo">Flight Coverage Map</label>

                <input type="checkbox" id="DetailedCoverageMap" name ="detailedCoverageMap" value='selected'>
                <label for="GeneralInfo">Detailed Coverage Map</label>

                <input type="checkbox" id="TermsAndDefinition" name ="termsAndDefinition" value='selected'>
                <label for="GeneralInfo">Terms And Definition</label>

                <input type="checkbox" id="Analysis" name ="analysis" value='selected'>
                <label for="GeneralInfo">Analysis</label>
            </div>
        </div>
        
        <button class="p"> Generate PDF</button>
    </form>
</div>

