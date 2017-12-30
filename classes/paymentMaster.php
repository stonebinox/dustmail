<?php
/*-------------------------------
Author: Anoop Santhanam
Date Created: 30/12/17 23:42
Last modified: 30/12/17 23:42
Comments: Main class file for 
payment_master table.
-------------------------------*/
class paymentMaster extends userMaster
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
}
?>