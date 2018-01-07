<?php
/*-----------------------------
Author: Anoop Santhanam
Last modified: 4/1/18 12:30
Comments: Main controller file.
-----------------------------*/
ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
function validate($object)
{
    if(($object!="")&&($object!=NULL))
    {
        return true;
    }
    return false;
}
function secure($string)
{
    return addslashes(htmlentities($string));
}
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
      'driver' => 'pdo_mysql',
      'dbname' => 'heroku_63674accd600246',
      'user' => 'bcf6b0acc3787b',
      'password' => '7288977d',
      'host'=> "us-cdbr-iron-east-05.cleardb.net",
    )
));
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/tmp/sessions'
));
$app->before(function(Request $request) use($app){
    $request->getSession()->start();
});
$app->get("/",function() use($app){
    $app['twig']->render("index.html.twig");
});
$app->post("/registration",function(Request $request){
    if(($request->get("name"))&&($request->get("email"))&&($request->get("password"))&&($request->get("password2"))&&($request->get("admin_id"))&&($request->get("location_lat"))&&($request->get("location_lon")))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster;
        $response=$user->createAccount($request->get("name"),$request->get("email"),$request->get("password"),$request->get("password2"),$request->get("admin_id"),$request->get("location_lat"),$request->get("location_lon"));
        return $response;
    }
    else
    {
        return "INVALID_PARAMETERS";
    }
});
$app->get("/comingsoon",function() use($app){
    return $app['twig']->render("comingsoon.html.twig");
});
$app->post("/login",function(Request $request) use($app){
    if(($request->get("user_email"))&&($request->get("user_password")))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster;
        $response=$user->authenticateUser($request->get("user_email"),$request->get("user_password"));
        return $response;
    }
    else
    {
        return "INVALID_PARAMETERS";
    }
});
$app->get("/verify",function(Request $request) use($app){
    if($request->get("id"))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster($request->get("id"));
        $response=$user->verifyAccount();
        if($response=="ACCOUNT_VERIFIED")
        {
            return $app->redirect("/?suc=ACCOUNT_VERIFIED");
        }
        else
        {
            return $app->redirect("/?err=".$response);
        }
    }
    else
    {
        return $app->redirect("/");
    }
});
$app->get("/user/getUser",function() use($app){
    if($app['session']->get("uid"))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster($app['session']->get("uid"));
        $userData=$user->getUser();
        if(is_array($userData))
        {
            return json_encode($userData);
        }
        return $userData;
    }
    else
    {
        return "INVALID_PARAMETERS";
    }
});
$app->get("/logout",function() use($app){
    if($app['session']->get("uid"))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster($app['session']->get("uid"));
        $response=$user->logout();
        return $app->redirect("/");
    }
    else
    {
        return $app->redirect("/");
    }
});
$app->post("/pay",function(Request $request) use($app){
    if(($app['session']->get("uid"))&&($request->get("stripeToken"))&&($request->get("subject"))&&($request->get("body"))&&($request->get("devcount"))&&($request->get("admin_id")))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        require("../classes/emailMaster.php");
        require("../classes/couponMaster.php");
        require("../classes/paymentMaster.php");
        \Stripe\Stripe::setApiKey("sk_test_0AkRhm58Zu4HwoPOLNM0uANj");
        $couponID=NULL;
        $amount=$request->get("devcount")/20;
        if($request->get("coupon_id"))
        {
            $couponID=secure($request->get("coupon_id"));
            $coupon=new couponMaster($couponID);
            $couponData=$coupon->getCoupon();
            if(is_array($couponData))
            {
                $couponType=$couponData['coupon_type'];
                $couponValue=$couponData['coupon_value'];
                if($couponType=="Percentage")
                {
                    $discount=$amount*($couponValue/100);
                    $amount=$amount-$discount;
                }
                elseif($couponType=="Value")
                {
                    $amount=$amount-$couponValue;
                }
            }
        }
        $amount=$amount*100;
        $token = $request->get('stripeToken');
        $charge = \Stripe\Charge::create(array(
        "amount" => $amount,
        "currency" => "usd",
        "description" => "Dust email campaign",
        "source" => $token,
        ));
        if($charge->failure_code!=NULL)
        {
            return $app->redirect("/?err=PAYMENT_ERROR_".$charge->failure_message);
        }
        $payment=new paymentMaster;
        $amount=$amount/100;
        $response=$payment->addPayment($app['session']->get("uid"),$amount,$token);
        if($response=="PAYMENT_ADDED")
        {
            $email=new emailMaster;
            $emailResponse=$email->sendEmails($app['session']->get("uid"),$request->get("subject"),$request->get("body"),$request->get("devcount"),$request->get("admin_id"));
            if($emailResponse=="USERS_EMAILED")
            {
                return $app->redirect("/?suc=".$emailResponse);
            }
            else
            {
                return $app->redirect("/?err=".$emailResponse);
            }
        }   
        else
        {
            return $app->redirect("/?err=".$response);
        }
    }
    else
    {
        return $app->redirect("/?err=INVALID_PARAMETERS");
    }
});
$app->get("/user/getAllUsers",function() use($app){
    require("../classes/adminMaster.php");
    require("../classes/userMaster.php");
    $user=new userMaster;
    $users=$user->getAllUsers();
    if(is_array($users))
    {
        return json_encode($users);
    }
    return $users;
});
$app->get("/import",function() use($app){
    require("../classes/adminMaster.php");
    require("../classes/userMaster.php");
    $content=file_get_contents("js/import.txt");
    $json=json_decode($content,true);
    echo count($json).'<br>';
    foreach($json as $user)
    {
        $emailID=$user['email'];
        $password=$user['password'];
        $devFlag=$user['isDeveloper'];
        if($devFlag)
        {
            $adminID=21;
        }
        else
        {
            $adminID=11;
        }
        $verifiedFlag=$user['verified'];
        if(validate($user['profile']))
        {
            $profile=$user['profile'];
            $userName=$profile['firstName'].' '.$profile['lastName'];
            $about=$profile['introduction'];
            if(validate($profile['locationLL']))
            {
                $coords=$profile['locationLL'];
                $coords=trim($coords,'"');
                $coords=ltrim($coords,'[');
                $coords=rtrim($coords,']');
                $e=explode(',',$coords);
                $latitude=$e[0];
                $longitude=$e[1];
            }
            $twitter=$profile['twitter'];
            $website=$profile['website'];
        }
        else
        {
            $userName=$emailID;
            $about='';
            $latitude=NULL;
            $longitude=NULL;
            $twitter=NULL;
            $website=NULL;
        }
        $userObj=new userMaster;
        $response=$userObj->importUser($emailID,$userName,$password,$adminID,$verifiedFlag,$about,$latitude,$longitude,$twitter,$website);
        echo $response.' - '.$emailID.'<br>';
    }
    return "DONE";
});
$app->get("/user/resetPassword",function(Request $request) use($app){
    if($request->get("pass_email"))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster;
        $userID=$user->getUserIDFromEmail($request->get("pass_email"));
        if(is_numeric($userID))
        {
            $user=new userMaster($userID);
            $resetLink='https://dustmail.herokuapp.com/reset/'.$userID;
            $userName=$user->getUserName();
            $subject='Reset your password';
            $body='Hi! Someone requested to reset your password. If this wasn\'t you, please ignore this email. If this was you, then click on the following link to reset your password: https://dustmail.herokuapp.com/?suc=RESET_PASSWORD&id='.$userID.' - Dust Team';
            $from = new SendGrid\Email("Dust", "noreply@dusthq.com");
            $to = new SendGrid\Email($userName, $request->get("pass_email"));
            $content = new SendGrid\Content("text/plain", $body);
            $mail = new SendGrid\Mail($from, $subject, $to, $content);
            // $apiKey = getenv('SENDGRID_API_KEY');
            $apiKey='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';
            $sg = new \SendGrid($apiKey);
            $response = $sg->client->mail()->send()->post($mail);
            return "RESET_LINK_SENT";
        }
        else
        {
            return $userID;
        }
    }
    else
    {
        return "INVALID_PARAMETERS";
    }
});
$app->post("/user/savePassword",function(Request $request) use($app){
    if(($request->get("npassword1"))&&($request->get("npassword2"))&&($request->get("uid")))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user=new userMaster($request->get("uid"));
        $response=$user->savePassword($request->get("npassword1"),$request->get("npassword2"));
        if($response=="PASSWORD_RESET")
        {
            return $app->redirect("/?suc=".$response);
        }
        else
        {
            return $app->redirect("/?err=".$response);
        }
    }
    else
    {
        return $app->redirect("/?err=INVALID_PARAMETERS");
    }
});
$app->get("/coupon/getRandomCoupon",function() use($app){
    require("../classes/adminMaster.php");
    require("../classes/userMaster.php");
    require("../classes/emailMaster.php");
    require("../classes/couponMaster.php");
    $coupon=new couponMaster;
    $couponData=$coupon->getRandomCoupon();
    if(is_array($couponData))
    {
        return json_encode($couponData);
    }
    return $couponData;
});
$app->get("/coupon/getCouponFromCode/{code}",function($code) use($app){
    if(validate($code))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        require("../classes/emailMaster.php");
        require("../classes/couponMaster.php");
        $coupon=new couponMaster;
        $couponID=$coupon->getCouponIDByCode($code);
        if(is_numeric($couponID))
        {
            $coupon=new couponMaster($couponID);
            $couponData=$coupon->getCoupon();
            if(is_array($couponData))
            {
                return json_encode($couponData);
            }
            return $couponData;
        }
        return $couponID;
    }
    else
    {
        return "INVALID_COUPON_CODE";
    }
});
$app->get("/monitor/hitMonitor/{monitorID}",function($monitorID) use($app){
    require("../classes/monitorMaster.php");
    $monitor=new monitorMaster($monitorID);
    $response=$monitor->hitMonitor();
    return $response;
});
$app->run();
?>