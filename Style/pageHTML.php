
<div class ="body">
    <form class ="form" action="../app/doGenerate.php" method="POST">
        <div class ="name">
            <label> User Name: </label>
            <input class="userid" type="text" name="username">
        </div>
        
        <div class="type">
            <label> User Type: </label>
            <select class ="option" name="type">
                <option>...</option>
                <option value ="worker"> Worker</option>
                <option value ="manager"> Manager </option>
            </select>
        </div>
        
        
        
        <div class ="inspection">
            <label> Inspections: </label>
            <select class="logs" name ="target">
                <option>...</option>
            </select>
        </div>
        
        <div class="printType">
            <lable> Print version</lable>
            <select class ="printOption" name="printType">
                <option>...</option>
                <option value ="WORKER"> Worker Version </option>
                <option value ="MANAGER"> Manager Version</option>
            </select>
        </div>
        <button class="p"> Generate PDF</button>
    </form>
</div>

