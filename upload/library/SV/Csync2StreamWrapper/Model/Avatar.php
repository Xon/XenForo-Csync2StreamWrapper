<?php

class SV_Csync2StreamWrapper_Model_Avatar extends XFCP_SV_Csync2StreamWrapper_Model_Avatar
{

    protected function _writeAvatar($userId, $size, $tempFile)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit();
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