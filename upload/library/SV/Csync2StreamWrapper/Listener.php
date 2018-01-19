<?php

class SV_Csync2StreamWrapper_Listener
{
    public static function init_dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        SV_Csync2StreamWrapper_csyncwrapper::RegisterStream();
    }

    public static function load_class_model($class, array &$extend)
    {
        $extend[] = 'SV_Csync2StreamWrapper_' . $class;
    }

    public static function load_class_patch($class, array &$extend)
    {
        $extend[] = 'SV_Csync2StreamWrapper_Patches_' . $class;
    }
}
