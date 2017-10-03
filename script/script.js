/*
 *Assumption :  username only combined by letter and number
 *Finction : check input username is valid or not
 * @param {string} username
 * @returns {boolean} true when username combined by letter and number 
 * else return false
 */
function validInput(username) {
    var array = username.split('');
    console.log(array);
    for (i = 0; i < array.length;) {
        var temp = array[i];
        if ((temp >= '0' && temp <= '9') || (temp >= 'A' && temp <= 'Z') || (temp >= 'a' && temp <= 'z')) {
            i++;
        } else {
            return false;
        }
    }
    return true;
}

/*
 * function: use ajax to get userInfo
 * @param {string} username 
 * @returns {var} an array contains username and usertype in success case
 * else return false
 */
function checkUser(username) {
    var select = $('.logs');
    var show = $(".option");
    var hide = $(".hide");
    // add sanity chech here
    var isValid = new validInput(username);
    if (isValid){
        $.ajax('getUserInfo.php', {
            data:{'username':username},
            success:function(response) {
                var obj = $.parseJSON(response);
                //console.log(obj);
                pageUserInfo.username = obj['userName'];
                //console.log(pageUserInfo.username);
                pageUserInfo.usertype = obj['userType'];
                //console.log(pageUserInfo.usertype);
                if (pageUserInfo.username != '') {
                    //Append according userType in option class
                    show.append($($('<option>', {
                        value:pageUserInfo.usertype,
                        text: pageUserInfo.usertype
                    })));
                    if (pageUserInfo.usertype == 'Worker') {// hide some element from worker
                        hied.css({'display':'none'});
                    }
                    new fetchdata(pageUserInfo.username,select);
                } else {
                    alert("Invalid user name! please input again");
                    location.reload();
                }
            },
            error: function(request,errorType,errorMessage){
                    console.log('Error: ' + errorType + ' with message: ' + errorMessage);
                }
        });
    } else {
        alert("Invalid user name! please input again");
        location.reload();
    }
}

/*
 * function: use jquery to show following steps based on number of logs
 * @param {type} options
 * @returns void
 */
function showSteps(options) {
    console.log("in showSteps");
    if (options >= 1) {
        $(".option").change(function(){
            var update = $(this).closest('.form');
            var show = update.find('.printType');
            var type = $(this).find(":selected").val();
            
            if (type == 'Manager' || type == 'Admin') {
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
    console.log("in fetchdata");
        $.ajax('getLogs.php',{
            data: {'username':input},
            success: function(response){
                console.log(response); 
                var obj = $.parseJSON(response);
                console.log(obj);
                for(var i = 0; i < obj.length;i++) {

                    select.append($($('<option>', {
                        value:obj[i],
                        text: obj[i]
                    })));
                }
                console.log(obj.length);
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
var pageUserInfo = {
    username:'',
    usertype:'',
}
var pageFunction = {
    init: function(){
        var check = $('.fetchdata');
        $('.userid').on('change', function(){
        var input=$(this);
        var data = input.val();
        check.on('click',new checkUser(data));
      });
    }
};

$(document).ready(function(){
    pageFunction.init();
});