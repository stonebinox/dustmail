<?php
/*------------------------------
Author: Anoop Santhanam
Date created: 7/1/18 13:14
Last modified: 7/1/18 13:14
Comments: Main class file for 
monitor_master table.
------------------------------*/
class monitorMaster
{
    public $app=NULL;
    public $monitorValid=false;
    private $monitor_id=NULL;
    function __construct($monitorID=NULL)
    {   
        $this->app=$GLOBALS['app'];
        if($monitorID!=NULL)
        {
            $this->monitor_id=secure($monitorID);
            $this->monitorValid=$this->verifyMonitor();
        }
    }
    function verifyMonitor()
    {
        if($this->monitor_id!=NULL)
        {
            $app=$this->app;
            $monitorID=$this->monitor_id;
            $mm="SELECT idmonitor_master FROM monitor_master WHERE stat='1' AND idmonitor_master='$monitorID'";
            $mm=$app['db']->fetchAssoc($mm);
            if(validate($mm))
            {
                return true;
            }
            return false;
        }
        return false;
    }
    function getMonitorHits()
    {
        if($this->monitorValid)
        {
            $monitorID=$this->monitor_id;
            $app=$this->app;
            $mm="SELECT hits FROM monitor_master WHERE idmonitor_master='$monitorID'";
            $mm=$app['db']->fetchAssoc($mm);
            if(validate($mm))
            {
                return $mm['hits'];
            }
            return "INVALID_MONITOR_ID";
        }
        return "INVALID_MONITOR_ID";
    }
    function hitMonitor()
    {
        if($this->monitorValid)
        {
            $monitorID=$this->monitor_id;
            $hits=$this->getMonitorHits();
            if(is_numeric($hits))
            {
                $hits+=1;
                $up="UPDATE monitor_master SET hits='$hits' WHERE idmonitor_master='$monitorID'";
                $up=$app['db']->executeUpdate($up);
                return "MONITOR_HIT";
            }
            return $hits;
        }
        return "INVALID_MONITOR_ID";
    }
}
?>