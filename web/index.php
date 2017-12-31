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
$app->post("/login",function() use($app){
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
$app->run();
?>