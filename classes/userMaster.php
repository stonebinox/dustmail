<?php
/*----------------------------------------
Author: Anoop Santhanam
Date Created: 22/10/17 18:41
Last Modified: 22/10/17 18:41
Comments: Main class file for user_master
table.
----------------------------------------*/
class userMaster extends adminMaster
{
    public $app=NULL;
    public $userValid=false;
    private $user_id=NULL;
    function __construct($userID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($userID!=NULL)
        {
            $this->user_id=addslashes(htmlentities($userID));
            $this->userValid=$this->verifyUser();
        }
    }
    function verifyUser() //to verify a user
    {
        if($this->user_id!=NULL)
        {
            $userID=$this->user_id;
            $app=$this->app;
            $um="SELECT admin_master_idadmin_master FROM user_master WHERE stat='1' AND iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                $adminID=$um['admin_master_idadmin_master'];
                adminMaster::__construct($adminID);
                if($this->adminValid)
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
    function getUser() //to get a user's details
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT * FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                $adminID=$um['admin_master_idadmin_master'];
                adminMaster::__construct($adminID);
                $admin=adminMaster::getAdmin();
                if(is_array($admin))
                {
                    $um['admin_master_idadmin_master']=$admin;
                }
                return $um;
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getUserIDFromEmail($userEmail)
    {
        $app=$this->app;
        $userEmail=addslashes(htmlentities($userEmail));
        $um="SELECT iduser_master FROM user_master WHERE stat='1' AND user_email='$userEmail'";
        $um=$app['db']->fetchAssoc($um);
        if(($um!="")&&($um!=NULL))
        {
            return $um['iduser_master'];
        }
        else
        {
            return "INVALID_USER_EMAIL";
        }
    }
    function getUserPassword()
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT user_password FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um['user_password'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getVerificationFlag()
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT email_flag FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(validate($um))
            {
                return $um['email_flag'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function authenticateUser($userEmail,$userPassword) //to log a user in
    {
        $userEmail=addslashes(htmlentities($userEmail));
        $userID=$this->getUserIDFromEmail($userEmail);

        $app=$this->app;
        if(is_numeric($userID))
        {
            $this->__construct($userID);
            $userPassword=md5($userPassword);
            $storedPassword=$this->getUserPassword();
            if($userPassword==$storedPassword)
            {
                $emailFlag=$this->getVerificationFlag();
                if($emailFlag==1)
                {
                    $up="UPDATE user_master SET online_flag='1' WHERE iduser_master='$userID'";
                    $up=$app['db']->executeUpdate($up);
                    $app['session']->set('uid',$userID);
                    return "AUTHENTICATE_USER";
                }
                else
                {
                    return "USER_NOT_VERIFIED";
                }
            }
            else
            {
                return "INVALID_USER_CREDENTIALS";
            }
        }
        else
        {
            return "INVALID_USER_CREDENTIALS";
        }
    }
    function createAccount($userName,$userEmail,$userPassword,$userPassword2,$adminID=1,$locationLat,$locationLon) //to create an account
    {
        $app=$this->app;
        $userName=trim(addslashes(htmlentities($userName)));
        if(($userName!="")&&($userName!=NULL))
        {  
            $userEmail=trim(addslashes(htmlentities($userEmail)));
            if(filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
                if(strlen($userPassword)>=8)
                {
                    if($userPassword===$userPassword2)
                    {
                        $adminID=addslashes(htmlentities($adminID));
                        adminMaster::__construct($adminID);
                        if($this->adminValid)
                        {
                            $um="SELECT iduser_master FROM user_master WHERE user_email='$userEmail' AND stat!='0'";
                            $um=$app['db']->fetchAssoc($um);
                            if(($um=="")||($um==NULL))
                            {
                                $hashPassword=md5($userPassword);
                                $in="INSERT INTO user_master (timestamp,user_name,user_email,user_password,admin_master_idadmin_master) VALUES (NOW(),'$userName','$userEmail','$hashPassword','$adminID')";
                                $in=$app['db']->executeQuery($in);
                                $um="SELECT iduser_master FROM user_master WHERE stat='1' AND user_email='$userEmail'";
                                $um=$app['db']->fetchAssoc($um);
                                $userID=$um['iduser_master'];
                                $from = new SendGrid\Email("Dust", "dust@dusthq.com");
                                $to = new SendGrid\Email($userName, $userEmail);
                                $e=explode(" ",$userName);
                                $firstName=trim($e[0]);
                                $content = new SendGrid\Content("text/plain", 'Hi '.$firstName.'. Thank you for signing up to Dust. Please click the following link to confirm your email: https://dustmail.herokuapp.com/verify?id='.$userID.' - The Dust Team');
                                $subject='Please confirm your email';
                                $mail = new SendGrid\Mail($from, $subject, $to, $content);
                                // $apiKey = getenv('SENDGRID_API_KEY');
                                $apiKey='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';
                                $sg = new \SendGrid($apiKey);
                                $response = $sg->client->mail()->send()->post($mail);
                                return "ACCOUNT_CREATED";
                            }
                            else
                            {
                                return "ACCOUNT_ALREADY_EXISTS";
                            }
                        }
                        else
                        {
                            return "INVALID_ADMIN_TYPE_ID";
                        }
                    }
                    else
                    {
                        return "PASSWORD_MISMATCH";
                    }
                }
                else
                {
                    return "INVALID_PASSWORD";
                }
            }
            else
            {
                return "INVALID_USER_EMAIL";
            }
        }
        else
        {
            return "INVALID_USER_NAME";
        }
    }
    function logout() //to log a user out
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="UPDATE user_master SET online_flag='0' WHERE iduser_master='$userID'";
            $um=$app['db']->executeUpdate($um);
            $app['session']->remove("uid");
            return "USER_LOGGED_OUT";
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getAdminType() //to get user's admin role
    {
        $app=$this->app;
        if($this->userValid)
        {
            $userID=$this->user_id;
            $um="SELECT admin_master_idadmin_master FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um['admin_master_idadmin_master'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getUsers($limit=10,$adminID=NULL)
    {
        $app=$this->app;
        $limit=secure($limit);
        if((validate($limit))&&(is_numeric($limit))&&($limit>0))
        {
            $um="SELECT iduser_master FROM user_master WHERE stat='1'";
            if(validate($adminID))
            {
                $adminID=secure($adminID);
                adminMaster::__construct($adminID);
                if($this->adminValid)
                {
                    $um.=" AND admin_master_idadmin_master='$adminID'";
                }
            }
            $um.=" ORDER BY RAND() LIMIT $limit";
            $um=$app['db']->fetchAll($um);
            $userArray=array();
            foreach($um as $user)
            {
                $userID=$user['idemail_master'];
                $this->__construct($userID);
                $userData=$this->getEmail();
                if(is_array($userData))
                {
                    array_push($userArray,$userData);
                }
            }
            if(count($userArray)>0)
            {
                return $userArray;
            }
            else
            {
                return "NO_USERS_FOUND";
            }
        }
        else
        {
            return "INVALID_USER_LIMIT";
        }
    }
    function getUserName()
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT user_name FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(validate($um))
            {
                return $um['user_name'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getRandomUser($adminID)
    {
        $adminID=secure($adminID);
        adminMaster::__construct($adminID);
        if($this->adminValid)
        {
            $userID=secure($userID);
            $this->__construct($userID);
            if($this->userValid)
            {
                $app=$this->app;
                $em="SELECT iduser_master FROM user_master WHERE stat='1' AND admin_master_idadmin_master='$adminID' ORDER BY RAND() LIMIT 1";
                $em=$pp['db']->fetchAssoc($em);
                if(validate($em))
                {
                    $userID2=$em['iduser_master'];
                    $this->__construct($userID2);
                    $user=$this->getUser();
                    if(is_array($user))
                    {
                        return $user;
                    }
                    else
                    {
                        return "NO_USERS_FOUND";
                    }
                }
                else{
                    return "NO_USERS_FOUND";
                }
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_ADMIN_ID";
        }
    }
    function verifyAccount()
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="UPDATE user_master SET email_flag='1' WHERE iduser_master='$userID'";
            $um=$app['db']->executeUpdate($um);
            return "ACCOUNT_VERIFIED";
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
}
?> 