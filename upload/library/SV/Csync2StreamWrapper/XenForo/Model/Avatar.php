<?php

class SV_Csync2StreamWrapper_XenForo_Model_Avatar extends XFCP_SV_Csync2StreamWrapper_XenForo_Model_Avatar
{

    protected function _writeAvatar($userId, $size, $tempFile)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_data]);
        try
        {
            return parent::_writeAvatar($userId, $size, $tempFile);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
}

if (false)
{
    class XFCP_SV_Csync2StreamWrapper_XenForo_Model_Avatar extends XenForo_Model_Avatar {}
}
