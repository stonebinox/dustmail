<?php
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
    if(($app['session']->get("uid"))&&($request->get("stripeToken"))&&($request->get("subject"))&&($request->get("body"))&&($request->get("devcount")))
    {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        require("../classes/emailMaster.php");
        require("../classes/paymentMaster.php");
        // $amount=$request->get("amount");
        \Stripe\Stripe::setApiKey("sk_test_0AkRhm58Zu4HwoPOLNM0uANj");
        $amount=($request->get("devcount")/10)*100;
        // Token is created using Checkout or Elements!
        // Get the payment token ID submitted by the form:
        $token = $request->get('stripeToken');
        
        // Charge the user's card:
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
            $emailResponse=$email->sendEmails($app['session']->get("uid"),$request->get("subject"),$request->get("body"),$request->get("devcount"));
            if($emailResponse=="USERS_EMAILED")
            {
                return $app->redirect("/?suc=".$emailResponse);
            }
            else
            {
                return $app->redirect("/?err=".$emailResponse);
                // return $emailResponse;
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
$app->run();
?>