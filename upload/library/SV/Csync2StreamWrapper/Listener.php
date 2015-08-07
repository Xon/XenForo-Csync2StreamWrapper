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
        $extend[] = self::AddonNameSpace.'_'.$class;
	}    
}
