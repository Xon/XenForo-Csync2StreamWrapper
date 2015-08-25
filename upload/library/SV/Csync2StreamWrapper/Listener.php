<?php

class SV_Csync2StreamWrapper_Listener
{
    const AddonNameSpace = 'SV_Csync2StreamWrapper_';

	public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
        SV_Csync2StreamWrapper_csyncwrapper::RegisterStream();
	}

	public static function load_class_model($class, array &$extend)
	{
        $extend[] = self::AddonNameSpace.$class;
	}

	public static function load_class_patch($class, array &$extend)
	{
        $extend[] = self::AddonNameSpace.'Patches_'.$class;
	}
}
