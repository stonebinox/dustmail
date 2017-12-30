<?php
/*----------------------------------
Author: Anoop Santhanam
Date Created: 30/12/17 22:59
Last modified: 30/12/17 22:59
Comments: Main class file for 
email_master table.
---------------------------------*/
class emailMaster extends userMaster
{
    public $app=NULL;
    private $email_id=NULL;
    public $emailValid=false;
    function __construct($emailID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if(validate($emailID))
        {
            $this->email_id=secure($emailID);
            $this->emailValid=$this->verifyEmail();
        }
    }
    function verifyEmail()
    {
        if($this->email_id!=NULL)
        {
            $app=$this->app;
            $emailID=$this->email_id;
            $em="SELECT user_master_iduser_master,email_user FROM email_master WHERE stat='1' AND idemail_master='$emailID'";
            $em=$app['db']->fetchAssoc($em);
            if(validate($em))
            {
                $userID=$em['user_master_iduser_master'];
                userMaster::__construct($userID);
                if($this->userValid)
                {
                    $userID2=$em['email_user'];
                    userMaster::__construct($userID2);
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
        else
        {
            return false;
        }
    }
    function getEmail()
    {  
        if($this->emailValid)
        {
            $app=$this->app;
            $emailID=$this->email_id;
            $em="SELECT * FROM email_master WHERE idemail_master='$emailID'";
            $em=$app['db']->fetchAssoc($em);
            if(validate($em))
            {
                $userID=$em['user_master_iduser_master'];
                userMaster::__construct($userID);
                $user=userMaster::getUser();
                if(is_array($user))
                {
                    $em['user_master_iduser_master']=$user;
                }
                $userID2=$em['email_user'];
                userMaster::__construct($userID2);
                $user2=userMaster::getUser();
                if(is_array($user2))
                {
                    $em['user_email']=$user2;
                }
                return $em;
            }
            else
            {
                return "INVALID_EMAIL_ID";
            }
        }
        else
        {
            return "INVALID_EMAIL_ID";
        }
    }    
}
?>