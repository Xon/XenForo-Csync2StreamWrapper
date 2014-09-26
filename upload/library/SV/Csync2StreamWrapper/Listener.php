<?php

class SV_Csync2StreamWrapper_Listener
{
	public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
        SV_Csync2StreamWrapper_csyncwrapper::RegisterStream();        
	}
    
	public static function load_class_model($class, array &$extend)
	{
    //XenForo_Template_FileHandler
        switch($class)
        {
            case 'XenForo_Model_Template':
                $extend[] = 'SV_Csync2StreamWrapper_Model_Template';
                break;
            case 'XenForo_Model_Avatar':
                $extend[] = 'SV_Csync2StreamWrapper_Model_Avatar';
                break;
            case 'Waindigo_InstallUpgrade_Model_InstallUpgrade':
                $extend[] = 'SV_Csync2StreamWrapper_Waindigo_Model_InstallUpgrade';
                break;                
            case 'XenForo_Model_AddOn':
                $extend[] = 'SV_Csync2StreamWrapper_Model_AddOn';
                break; 
            case 'XenForo_Model_Sitemap':
                $extend[] = 'SV_Csync2StreamWrapper_Model_Sitemap';
                break;
        }
	}    
}
