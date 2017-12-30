var app=angular.module("dust",[]);
app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
app.controller("mail",function($scope,$compile,$http){
    $scope.map=null;
    $scope.admin_id=null;
    $scope.loadMap=function(){
        var mapProp= {
            center:new google.maps.LatLng(51.508742,-0.120850),
            zoom:3,
            mapTypeId: 'roadmap'
        };
        $scope.map=new google.maps.Map(document.getElementById("map"),mapProp);
        var width=$(window).width();
        var height=$(window).height();
        $("#map").css({
            width: width+"px",
            height: height+"px"
        });
        $(".form").css("height",(height-51)+"px");
        var text='<table width="100%" cellpadding=0 cellspacing=10 align="center">';
        for(var i=1;i<=15;i++){
            if((i==1)||(i==6)||(i==11)){
                text+='<tr>';
            }
            text+='<td align="center" width="20%"><img src="images/credentials/'+i+'.png" class="img-responsive" style="width:80px;margin-bottom:10px;"></td>';
            if((i==5)||(i==10)||(i==15)){
                text+='</tr>';
            }
        }
        text+='</table>';
        $("#credlist").html(text);
    };
    $scope.registerUser=function(){
        if(validate($scope.admin_id)){
            var name=$.trim($("#name").val());
            if(validate(name)){
                $("#name").parent().removeClass("has-error");
                var email=$.trim($("#email").val());
                if(validate(email)){
                    $("#email").parent().removeClass("has-error");
                    var password=$("#password").val();
                    if(password.length>=8){
                        $("#password").parent().removeClass("has-error");
                        var password2=$("#password2").val();
                        if(password2===password){
                            $("#password").parent().removeClass("has-error");    
                            $("#password2").parent().removeClass("has-error");
                            $.ajax({
                                url: '/registration',
                                method: 'post',
                                error: function(response){
                                    console.log(response);
                                    $("#sendbut").removeClass("disabled");
                                    messageBox("Problem","Somethign went wrong while trying to create your account. Please try again later.");
                                },
                                success: function(response){
                                    console.log(response);
                                    $("#sendbut").removeClass("disabled");
                                    response=$.trim(response);
                                    switch(response){
                                        case "INVALID_PARAMETERS":
                                        case "INVALID_ADMIN_TYPE_ID":
                                        default:
                                        messageBox("Problem","Somethign went wrong while trying to create your account. Please try again later. This is the error we see: "+response);
                                        break;
                                        case "INVALID_USER_NAME":
                                        messageBox("Invalid Name","Please enter a valid name and try again.");
                                        break;
                                        case "INVALID_USER_EMAIL":
                                        messageBox("Invalid Email","Please enter a valid email ID and try again.");
                                        break;
                                        case "INVALID_PASSWORD":
                                        messageBox("Invalid Password","Please enter a valid password of at least 8 characters and try again.");
                                        break;
                                        case "PASSWORD_MISMATCH":
                                        messageBox("Password Mismatch","Please repeat the password correctly and try again.");
                                        break;
                                        case "ACCOUNT_ALREADY_EXISTS":
                                        messageBox("Account Exists","An account with this email ID already exists. Sign in to your account if that's you.");
                                        break;
                                        case "ACCOUNT_CREATED":
                                        mover('login');
                                        messageBox("Account Created","Your account was created successfully. Please check your email and verify your account to login.");
                                        break;
                                    }
                                },
                                beforeSend:function(){
                                    $("#sendbut").addClass("disabled");
                                }
                            });
                        }
                        else{
                            $("#password").parent().addClass("has-error");    
                            $("#password2").parent().addClass("has-error");
                        }
                    }
                    else{
                        $("#password").parent().addClass("has-error");
                    }
                }
                else{
                    $("#email").parent().addClass("has-error");    
                }
            }
            else{
                $("#name").parent().addClass("has-error");
            }
        }
    };
});
window.resize=function(){
    var width=$(window).width();
    var height=$(window).height();
    $("#map").css({
        width: width+"px",
        height: height+"px"
    });
    $("#form").css("height",(height-51)+"px");
}