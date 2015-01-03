<?php

class SV_Csync2StreamWrapper_CsyncConfig
{
    public function __construct()
    {
        //setlocale(LC_CTYPE, "en_US.UTF-8");
        //$this->debug_log 
        //$this->csync_database = "/var/lib/csync2";
    }

        
    public $deferred_count = 0;
    public $deferred_commit_bulk = false;
    public $deferred_paths = array();    
    public $deferred_files =  array();  
    public $csync2_binary = "/usr/sbin/csync2";    
    public $csync_database = ""; //= "/var/lib/csync2";
    
    public $debug_mode = false;
    public $debug_log = "";// = "/var/www/html/error.log";
    
    
    protected $_installed = false;
    
    public function isInstalled()
    {
        return $this->_installed;
    }

    public function setInstalled($_installed)
    {
        $this->_installed = $_installed;
    }  
    
    protected static $_instance;
    
    public static function getInstance()
    {
		if (!self::$_instance)
		{
			self::$_instance = new self();
		}

		return self::$_instance;    
    }
}