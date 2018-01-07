/*-------------------------------------
Author: Dust Team
Last Modified: 4/1/18 12:30
Comments: Main JS file.
-------------------------------------*/
var app=angular.module("dust",[]);
app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
app.controller("mail",function($scope,$compile,$http){
    $scope.map=null;
    $scope.admin_id=null;
    $scope.userArray=[];
    $scope.checkURL=function(){
        var url=window.location.href;
        if(url.indexOf("https://")==0){
            //do nothing
        }
        else{
            window.location="https://www.dusthq.com/";
        }
    };
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
        $("#devcredlist").html(text);
    };
    $scope.getCurrentLocation=function(){
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition($scope.saveUserLocation);
        } else {
            messageBox("Location Support","Looks like location services are not supported by your browser.");
        }
    };
    $scope.location=null;
    $scope.saveUserLocation=function(position){
        $scope.location=position;
        $("#location").html('<span class="text-info">Location stored</span>');
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
                            if(validate($scope.location)){
                                $.ajax({
                                    url: '/registration',
                                    method: 'post',
                                    data:{
                                        name: name,
                                        email: email,
                                        password: password,
                                        password2: password2,
                                        admin_id: $scope.admin_id,
                                        location_lat: $scope.location.coords.latitude,
                                        location_lon: $scope.location.coords.longitude
                                    },
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
                                            $scope.getAllUsers();
                                            break;
                                        }
                                    },
                                    beforeSend:function(){
                                        $("#sendbut").addClass("disabled");
                                    }
                                });
                            }
                            else{
                                messageBox("Location","Please select your current location and try again.");
                            }
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
    $scope.loginStatus=false;
    $scope.user=null;
    $scope.loginUser=function(){
        var email=$.trim($("#user_email").val());
        if(validate(email)){
            $("#user_email").parent().removeClass("has-error");
            var password=$("#user_password").val();
            if(password.length>=8){
                $("#user_password").parent().removeClass("has-error");
                $.ajax({
                    url: '/login',
                    method: 'post',
                    data:{
                        user_email: email,
                        user_password: password
                    },
                    error:function(response){
                        console.log(response);
                        messageBox("Problem","Something went wrong while logging you in. Please try again later.");
                    },
                    success: function(response){
                        response=$.trim(response);
                        switch(response){
                            case "INVALID_PARAMETERS":
                            default:
                            messageBox("Problem","Something went wrong while logging in. Please try again later. This is the error we see: "+response);
                            break;
                            case "INVALID_USER_CREDENTIALS":
                            messageBox("Invalid Credentials","Please verify the details and try again.");
                            break;
                            case "USER_NOT_VERIFIED":
                            messageBox("Not Verified","Your account hasn't been verified by you yet. Please check your email and click on the verification link to login.");
                            break;
                            case "AUTHENTICATE_USER":
                            $scope.loginStatus=true;
                            $scope.getUser();
                            break;
                        }
                    },
                    beforeSend:function(){
                        $("#loginbut").addClass("disabled");
                    }
                });
            }
            else{
                $("#user_password").parent().addClass("has-error");
            }
        }
        else{
            $("#user_email").parent().addClass("has-error");
        }
    };
    $scope.getUser=function(){
        $http.get("user/getUser")
        .then(function success(response){
            response=response.data;
            if(typeof response=="object"){
                $scope.user=response;
                $scope.loginStatus=true;
                $scope.loadUser();
            }
            else{
                response=$.trim(response);
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something went wrong while getting user information. Please try again later.");
        });
    };
    $scope.loadUser=function(){
        if(validate($scope.user)){
            var admin=$scope.user.admin_master_idadmin_master;
            var adminID=admin.idadmin_master;
            if(adminID==21){
                mover('devhome');
                $("#logo").click(function(){
                    mover('devhome');
                });
            }
            else if(adminID==11){
                mover("find");
            }
            $(".loginoptions").parent().css("display","none");
            $("#logoutbutton").parent().css("display","block");
            if(validate(localStorage.getItem("subject"))){
                $("#subject").val(localStorage.getItem("subject"));
                $("#desc").val(localStorage.getItem("body"));
            }
        }
    };
    $scope.validateRequest=function(){
        var subject=$.trim($("#subject").val());
        if(validate(subject)){
            $("#subject").parent().removeClass("has-error");
            $("#subjectmessage").html('');
            var body=$.trim($("#desc").val());
            if(validate(body)){
                $("#desc").parent().removeClass("has-error");
                $("#bodymessage").html('');
                var devcount=parseInt($scope.devcount);
                $scope.saveRequest(subject,body);
            }
            else{
                $("#desc").parent().addClass("has-error");
                $("#bodymessage").html('Email body is required.');
            }
        }
        else{
            $("#subject").parent().addClass("has-error");
            $("#subjectmessage").html('Email subject is required.');
        }
    };
    $scope.saveRequest=function(subject,body){
        if (typeof(Storage) !== "undefined") {
            localStorage.setItem("subject", subject);
            localStorage.setItem("body",body);
        }
        if($scope.loginStatus){
            var form=document.createElement("form");
            $(form).attr("method","post");
            $(form).attr("action","pay");
                var hidden1=document.createElement("input");
                $(hidden1).attr("type","hidden");
                $(hidden1).attr("name","subject");
                $(hidden1).attr("value",subject);
            $(form).append(hidden1);
                var hidden2=document.createElement("input");
                $(hidden2).attr("type","hidden");
                $(hidden2).attr("name","body");
                $(hidden2).attr("value",body);
            $(form).append(hidden2);
                var hidden3=document.createElement("input");
                $(hidden3).attr("type","hidden");
                $(hidden3).attr("name","devcount");
                $(hidden3).attr("value",$scope.devcount);
            $(form).append(hidden3);
            var amount=$scope.devcount/20;
            if(validate($scope.coupon_id)){
                var couponType=$scope.coupon_id.coupon_type;
                var couponValue=parseFloat($scope.coupon_id.coupon_value);
                if(couponType=="Percentage"){
                    var discount=amount*(couponValue/100);
                    amount=amount-discount;
                }
                else if(couponType=="Value"){
                    amount=amount-couponValue;
                }
                var text='';
                if(amount==0){
                    text='Your order is free. We still need you to just validate your card details to ensure you\'re not a spammer.';
                }
                var hidden4=document.createElement("input");
                $(hidden4).attr("type","hidden");
                $(hidden4).attr("name","coupon_id");
                $(hidden4).attr("id","coupon_id");
                $(hidden4).attr("value",$scope.coupon_id.idcoupon_master);
                $(form).append(hidden4);
                $(form).append('<div class="alert alert-success"><strong>Coupon Applied</strong> The coupon code has been applied! '+text+'</div>');
            }
            amount=amount*100;
            $(form).append('<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="pk_live_PSwDNF9BVOIuKl5BBzVXnXsa" data-amount="'+amount+'" data-name="Dust &amp; Co., Inc." data-description="Widget" data-image="images/dust-logo.png" data-locale="auto"></script>');
            messageBox("Make Payment",form);
        }
        else{
            $scope.admin_id=11;
            $scope.signText='';
            mover('registration');
        }
    };
    $scope.getAllUsers=function(){
        $http.get("user/getAllUsers")
        .then(function success(response){
            response=response.data;
            if(typeof response=="object"){
                $scope.userArray=response;
                $scope.displayAllUsers();
            }
            else{
                response=$.trim(response);
                switch(response){
                    case "INVALID_PARAMETERS":
                    default:
                    messageBox("Problem","Something went wrong while loading some information. Please try again later. This is the error we see: "+response);
                    break;
                    case "NO_USERS_FOUND":
                    //do nothing
                    break;
                }
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something wentg wrong while loading some information. Please try again later.");
        });
    };
    $scope.displayAllUsers=function(){
        if($scope.userArray.length>0){
            for(var i=0;i<$scope.userArray.length;i++){
                var user=$scope.userArray[i];
                var userID=user.iduser_master;
                var userName=stripslashes(user.user_name);
                var latitude=user.latitude;
                var longitude=user.longitude;
                var admin=user.admin_master_idadmin_master;
                var adminID=admin.idadmin_master;
                if(adminID==21){
                    var position={lat: parseFloat(latitude), lng: parseFloat(longitude)};
                    var marker = new google.maps.Marker({
                        position: position,
                        map: $scope.map,
                        title: userName
                    });
                }
            }
        }
    };
    $scope.validateReset=function(){
        var email=$.trim($("#pass_email").val());
        if(validate(email)){
            $("#pass_email").parent().removeClass("has-error");
            $http.get("user/resetPassword?pass_email="+email)
            .then(function success(response){
                response=$.trim(response.data);
                console.log(response);
                switch(response){
                    case "INVALID_PARAMETERS":
                    default:
                    messageBox("Problem","Something went wrong while sending you a reset link. Please try again later. This is the error we see: "+response);
                    break;
                    case "RESET_LINK_SENT":
                    case "INVALUD_USER_EMAIL":
                    messageBox("Link Sent","If the email ID matches the one we have on our records, you'll receive an email with a reset link. Please check your email inbox.");
                    $("#pass_email").val('');
                    break;
                }
            },
            function error(response){
                console.log(response);
                messageBox("Problem","Something went wrong while sending you a reset link. Please try again later.");
            });
        }
        else{
            $("#pass_email").parent().addClass("has-error");
        }
    };
    $scope.validatePasswords=function(){
        var uid=$("#uid").val();
        if(validate(uid)){
            var password1=$("#npassword1").val();
            if(password1.length>=8){
                $("#npassword1").parent().removeClass("has-error");
                var password2=$("#npassword2").val();
                if(password2==password1){
                    $("#npassword1").parent().removeClass("has-error");
                    $("#npassword2").parent().removeClass("has-error");
                    document.confirmforgot.submit();
                }
                else{
                    $("#npassword1").parent().addClass("has-error");
                    $("#npassword2").parent().addClass("has-error");
                }
            }
            else{
                $("#npassword1").parent().addClass("has-error");
            }
        }
        else{
            messageBox("Problem",'Something went wrong while resetting your password. Please contact support at <a href="mailto:dust@dusthq.com">dust@dusthq.com</a> and our password manager would help.');
        }
    };
    $scope.compileSwitch=function(){
        $compile("#switch")($scope);
    };
    $scope.getRandomCoupon=function(){
        $scope.coupon=null;
        $http.get("coupon/getRandomCoupon")
        .then(function success(response){
            response=response.data;
            if(typeof response=="object"){
                $scope.coupon=response;
                $scope.showCoupon();
            }
            else{
                response=$.trim(response);
                switch(response){
                    case "INVALID_PARAMETERS":
                    default:
                    // messageBox("Problem","Something went wrong while loading coupons. Please try again later. This is the error we see: "+response);
                    break;
                }
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something went wrong while loading coupons. Please try again later.");
        });
    };
    $scope.showCoupon=function(){
        if(validate($scope.coupon)){
            var couponID=$scope.coupon.idcoupon_master;
            var couponText=$scope.coupon.coupon_name;
            var couponCode=$scope.coupon.coupon_code;
            var couponExpiry=$scope.coupon.expiry;
            couponExpiry=dateFormat(couponExpiry);
            $("#coupon").html('<span class="small text-info">'+couponText+'&nbsp;&bull;&nbsp;<strong>'+couponCode+'</strong>&nbsp;&bull;&nbsp;Expires on <strong>'+couponExpiry+'</strong></span>');
        }
    };
    $scope.coupon_id=null;
    $scope.checkCode=function(){
        var code=$.trim($("#couponcode").val());
        $("#couponcode").parent().removeClass("has-success has-feedback");
        $("#couponcode").parent().find('span').remove();
        if(validate(code)){
            code=code.toUpperCase();
            $http.get("coupon/getCouponFromCode/"+code)
            .then(function success(response){
                response=response.data;
                if(typeof response=="object"){
                    $scope.coupon_id=response;
                    $("#couponcode").parent().addClass("has-success has-feedback");
                    $("#couponcode").parent().append('<span class="glyphicon glyphicon-ok form-control-feedback"></span>');
                }
                else{
                    response=$.trim(response);
                    switch(response){
                        case "INVALID_PARAMETERS":
                        default:
                        messageBox("Problem","Something went wrong while checking this coupon code. Please try again later. This is the error we see: "+response);
                        break;
                        case "INVALID_COUPON_CODE":
                        messageBox("Invalid Code","The coupon code is invalid.");
                        $("#couponcode").val('');
                        break;
                    }
                }
            },
            function error(response){
                console.log(response);
                messageBox("Problem","Something went wrong while checking this coupon code. Please try again later.");
            });
        }
    };
    $scope.validateDevRequest=function(){
        var subject=$.trim($("#devsubject").val());
        if(validate(subject)){
            $("#devsubject").parent().removeClass("has-error");
            $("#devsubjectmessage").html('');
            var body=$.trim($("#devdesc").val());
            if(validate(body)){
                $("#devdesc").parent().removeClass("has-error");
                $("#devbodymessage").html('');
                var nondevcount=parseInt($scope.nondevcount);
                $scope.saveDevRequest(subject,body);
            }
            else{
                $("#devdesc").parent().addClass("has-error");
                $("#devbodymessage").html('Email body is required.');
            }
        }
        else{
            $("#devsubject").parent().addClass("has-error");
            $("#devsubjectmessage").html('Email subject is required.');
        }
    };
    $scope.saveDevRequest=function(subject,body){
        console.log("here");
        if (typeof(Storage) !== "undefined") {
            localStorage.setItem("subject", subject);
            localStorage.setItem("body",body);
        }
        if($scope.loginStatus){
            var form=document.createElement("form");
            $(form).attr("method","post");
            $(form).attr("action","pay");
                var hidden1=document.createElement("input");
                $(hidden1).attr("type","hidden");
                $(hidden1).attr("name","subject");
                $(hidden1).attr("value",subject);
            $(form).append(hidden1);
                var hidden2=document.createElement("input");
                $(hidden2).attr("type","hidden");
                $(hidden2).attr("name","body");
                $(hidden2).attr("value",body);
            $(form).append(hidden2);
                var hidden3=document.createElement("input");
                $(hidden3).attr("type","hidden");
                $(hidden3).attr("name","nondevcount");
                $(hidden3).attr("value",$scope.devcount);
            $(form).append(hidden3);
            var amount=$scope.devcount/20;
            if(validate($scope.coupon_id)){
                var couponType=$scope.coupon_id.coupon_type;
                var couponValue=parseFloat($scope.coupon_id.coupon_value);
                if(couponType=="Percentage"){
                    var discount=amount*(couponValue/100);
                    amount=amount-discount;
                }
                else if(couponType=="Value"){
                    amount=amount-couponValue;
                }
                var text='';
                if(amount==0){
                    text='Your order is free. We still need you to just validate your card details to ensure you\'re not a spammer.';
                }
                var hidden4=document.createElement("input");
                $(hidden4).attr("type","hidden");
                $(hidden4).attr("name","coupon_id");
                $(hidden4).attr("id","coupon_id");
                $(hidden4).attr("value",$scope.coupon_id.idcoupon_master);
                $(form).append(hidden4);
                $(form).append('<div class="alert alert-success"><strong>Coupon Applied</strong> The coupon code has been applied! '+text+'</div>');
            }
            amount=amount*100;
            $(form).append('<script src="https://checkout.stripe.com/checkout.js" class="stripe-button" data-key="pk_live_PSwDNF9BVOIuKl5BBzVXnXsa" data-amount="'+amount+'" data-name="Dust &amp; Co., Inc." data-description="Widget" data-image="images/dust-logo.png" data-locale="auto"></script>');
            messageBox("Make Payment",form);
        }
        else{
            $scope.admin_id=21;
            $scope.signText='';
            mover('registration');
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