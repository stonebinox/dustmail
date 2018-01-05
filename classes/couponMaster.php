<?php
/*----------------------------
Author: Anoop Santhanam
Date Created: 5/1/18 08:56
Last modified: 5/1/18 08:56
Comments: Main class file for 
coupon_master table.
----------------------------*/
class coupon_master extends emailMaster
{
    public $app=NULL;
    public $couponValid=false;
    private $Coupon_id=NULL;
    function __construct($couponID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($couponID!=NULL)
        {
            $this->coupon_id=secure($couponID);
            $this->couponValid=$this->verifyCoupon();
        }
    }
    function verifyCoupon()
    {
        if($this->coupon_id!=NULL)
        {
            $app=$this->app;
            $couponID=$this->coupon_id;
            $cm="SELECT idcoupon_master FROM coupon_master WHERE stat='1' AND idcoupon_master='$couponID'";
            $cm=$app['db']->fetchAssoc($cm);
            if(validate($cm))
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
    function getCouponIDByCode($code)
    {
        $app=$this->app;
        $code=secure($code);
        if(validate($code))
        {
            $cm="SELECT idcoupon_master FROM coupon_master WHERE stat='1' AND coupon_code='$code'";
            $cm=$app['db']->fetchAssoc($cm);
            if(validate($cm))
            {
                return $cm['idcoupon_master'];
            }
            else
            {
                return "INVALID_COUPON_CODE";
            }
        }
        else
        {
            return "INVALID_COUPON_CODE";
        }
    }
}
?>