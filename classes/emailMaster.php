<?php
/*----------------------------------
Author: Anoop Santhanam
Date Created: 30/12/17 22:59
Last modified: 4/1/18 12:30
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
    function checkUserEmailHistory($fromUser,$toUser,$subject)
    {
        $fromUser=secure($fromUser);
        userMaster::__construct($fromUser);
        if($this->userValid)
        {
            $toUser=secure($toUser);
            userMaster::__construct($toUser);
            if($this->userValid)
            {
                $app=$this->app;
                $subject=trim(secure($subject));
                $em="SELECT idemail_master FROM email_master WHERE stat='1' AND user_master_iduser_master='$fromUser' AND email_user='$toUser' AND email_subject='$subject'";
                $em=$app['db']->fetchAssoc($em);
                if(validate($em))
                {
                    return "EMAIL_ALREADY_SENT";
                }
                else
                {
                    return "NO_EMAIL_SENT";
                }
            }
            else
            {
                return "INVALID_TO_USER_ID";
            }
        }
        else
        {
            return "INVALID_FROM_USER_ID";
        }
    }
    function sendEmails($userID,$subject,$body,$limit=10,$adminID=21)
    {
        $app=$this->app;
        $adminID=secure($adminID);
        adminMaster::__construct($adminID);
        if($this->adminValid)
        {
            $limit=secure($limit);
            if((validate($limit))&&(is_numeric($limit))&&($limit>=0))
            {
                $userID=secure($userID);
                userMaster::__construct($userID);
                if($this->userValid)
                {
                    $subject=trim($subject);
                    if(validate($subject))
                    {
                        $body=trim($body);
                        if(validate($body))
                        {
                            $users=userMaster::getUsers($limit,$adminID);
                            if(is_array($users))
                            {
                                foreach($users as $user)
                                {
                                    $toUserID=$user['iduser_master'];
                                    $subFlag=$user['subscribe_flag'];
                                    if($subFlag==1)
                                    {
                                        $status=$this->checkUserEmailHistory($userID,$toUserID,$subject);
                                        if($status=="NO_EMAIL_SENT")
                                        {
                                            userMaster::__construct($userID);
                                            $senderName=userMaster::getUserName();
                                            $senderEmail=userMaster::getUserEmail();
                                            $body.=' To reply to '.$senderName.', please email '.$senderEmail.' instead of replying to this email directly. You cannot contact this sender by replying to this email.';
                                            $userEmail=$user['user_email'];
                                            $userName=stripslashes($user['user_name']);
                                            $from = new SendGrid\Email($senderName." via Dust", "dust@dusthq.com");
                                            $to = new SendGrid\Email($userName, $userEmail);
                                            $content = new SendGrid\Content("text/plain", $body);
                                            $mail = new SendGrid\Mail($from, $subject, $to, $content);
                                            // $apiKey = getenv('SENDGRID_API_KEY');
                                            $apiKey='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';
                                            $sg = new \SendGrid($apiKey);
                                            $response = $sg->client->mail()->send()->post($mail);
                                            $subject=secure($subject);
                                            $in="INSERT INTO email_master (timestamp,user_master_iduser_master,email_user,email_subject) VALUES (NOW(),'$userID','$toUserID','$subject')";
                                            $in=$app['db']->executeQuery($in);
                                        }
                                        elseif($status=="EMAIL_ALREADY_SENT")
                                        {
                                            do{
                                                $randomUser=userMaster::getRandomUser($adminID);
                                                if(is_array($randomUser))
                                                {
                                                    $toUserID=$randomUser['iduser_master'];
                                                    $eStatus=$this->checkUserEmailHistory($userID,$toUserID,$subject);
                                                    if($eStatus=="NO_EMAIL_SENT")
                                                    {
                                                        array_push($users,$randomUser);
                                                        $continue=false;
                                                    }
                                                    elseif($eStatus=="EMAIL_ALREADY_SENT")
                                                    {
                                                        $continue=true;
                                                    }
                                                    else
                                                    {
                                                        $continue=false;
                                                    }
                                                }
                                                else
                                                {
                                                    $continue=false;
                                                }
                                            }while($continue);
                                        }
                                    }
                                }
                                return "USERS_EMAILED";
                            }
                            else
                            {
                                return $users;
                            }
                        }
                        else
                        {
                            return "INVALID_EMAIL_BODY";
                        }
                    }
                    else
                    {
                        return "INVALID_EMAIL_SUBJECT";
                    }
                }
                else
                {
                    return "INVALID_USER_ID";
                }
            }
            else
            {
                return "INVALID_EMAIL_LIMIT";
            }
        }
        else
        {
            return "INVALID_ADMIN_ID";
        }
    }
}
?>