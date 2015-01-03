<?php

class SV_Csync2StreamWrapper_Listener
{
    const AddonNameSpace = 'SV_Csync2StreamWrapper';
    
	public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
        SV_Csync2StreamWrapper_csyncwrapper::RegisterStream();        
	}
    
	public static function load_class_model($class, array &$extend)
	{
        switch($class)
        {
            case 'XenForo_Model_Template':
            case 'XenForo_Model_Avatar':
            case 'XenForo_Model_AddOn':
            case 'XenForo_Model_Sitemap':
            case 'Waindigo_InstallUpgrade_Model_InstallUpgrade':
                $extend[] = self::AddonNameSpace.'_'.$class;
                break;
        }
	}    
}
