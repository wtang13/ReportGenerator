/*
 * function: use jquery to show following steps based on number of logs
 * @param {type} options
 * @returns void
 */
function showSteps(options) {
    if (options > 1) {
            
        $(".option").change(function(){
            var update = $(this).closest('.form');
            var show = update.find('.printType');
            var type = $(this).find(":selected").val();
      
            if (type == 'manager') {
            // show choice for manager
                show.css({'display':'inline'});
            }
            update.find(".p").css({'display':'inline'});
        }); 
        
    } else {
        alert('No flight history, please try again ot in latter!');
    }
}
/*
 * function: use ajax to fetch data from database then append all echo data in getLog.php
 * @param {type} input
 * @param {type} select
 * @returns void
 */
function fetchdata(input,select) {
    $.ajax('getLogs.php',{
        data: {'username':input},
        success: function(response){
            var obj = $.parseJSON(response);
            for(var i = 0; i < obj.length;i++) {
                
                select.append($($('<option>', {
                    value:obj[i],
                    text: obj[i]
                })));
            }
            new showSteps(obj.length);
        },
        error: function(request,errorType,errorMessage){
            console.log('Error: ' + errorType + ' with message: ' + errorMessage);
        }
    });
}

/*
 * Page Functions:
 * init: When user click fetch data button, do fetch data and append, 
 * if no data, alert and request to try again
 * if has data, then show following steps: when user input user type, get input. 
 * based on input type, show print pdf button or request more information
 */
var pageFunction = {
    init: function(){
        $('.userid').on('change', function(){
        var input=$(this);
        var data = input.val();
        var select = $(this).closest('.form').find('.logs');
        $('.fetchdata').on('click',new fetchdata(data,select));
      });
    }
};

$(document).ready(function(){
    pageFunction.init();
});