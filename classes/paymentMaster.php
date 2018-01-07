<?php
/*-------------------------------
Author: Anoop Santhanam
Date Created: 30/12/17 23:42
Last modified: 4/1/18 12:30
Comments: Main class file for 
payment_master table.
-------------------------------*/
class paymentMaster extends couponMaster
{
    public $app=NULL;
    public $paymentValid=false;
    private $payment_id=NULL;
    function __construct($paymentID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($paymentID!=NULL)
        {
            $this->payment_id=secure($paymentID);
            $this->paymentValid=$this->verifyPayment();
        }
    }
    function verifyPayment()
    {
        $app=$this->app;
        if($this->payment_id!=NULL)
        {
            $paymentID=$this->payment_id;
            $pm="SELECT user_master_iduser_master FROM payment_master WHERE stat='1' AND idpayment_master='$paymentID'";
            $pm=$app['db']->fetchAssoc($pm);
            if(validate($pm))
            {
                $userID=$pm['user_master_iduser_master'];
                userMaster::__construct($userID);
                if($this->userValid)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    function addPayment($userID,$amount,$token,$couponID=NULL)
    {
        $userID=secure($userID);
        $app=$this->app;
        userMaster::__construct($userID);
        if($this->userValid)
        {
            $amount=secure($amount);
            if((validate($amount))&&(is_numeric($amount))&&($amount>=0))
            {
                $token=secure($token);
                if(validate($token))
                {
                    if(validate($couponID))
                    {
                        $couponID=secure($couponID);
                        couponMaster::__construct($couponID);
                        if(!$this->couponValid)
                        {
                            $couponID=NULL;
                        }
                    }
                    $in="INSERT INTO payment_master (timestamp,user_master_iduser_master,amount,stripe_token) VALUES (NOW(),'$userID','$amount','$token')";
                    $in=$app['db']->executeQuery($in);
                    if(validate($couponID))
                    {
                        $pm="SELECT idpayment_master FROM payment_master WHERE stat='1' AND user_master_iduser_master='$userID' ORDER BY idpayment_master DESC LIMIT 1";
                        $pm=$app['db']->fetchAssoc($pm);
                        $paymentID=$pm['idpayment_master'];
                        $up="UPDATE payment_master SET coupon_master_idcoupon_master='$couponID' WHERE idpayment_master='$paymentID'";
                        $up=$app['db']->executeUpdate($up);
                    }
                    return "PAYMENT_ADDED";
                }
                else
                {
                    return "INVALID_STRIPE_TOKEN";
                }
            }
            else
            {
                return "INVALID_AMOUNT";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
}
?>