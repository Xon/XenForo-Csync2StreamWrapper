<?php

class SV_Csync2StreamWrapper_Waindigo_Model_InstallUpgrade extends XFCP_SV_Csync2StreamWrapper_Waindigo_Model_InstallUpgrade
{
	public function extractFromFile($fileName, $type = '')
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::extractFromFile($fileName, $type);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    } 

	public function checkForAddOnUpdates(array &$addOns, &$errorString = null)
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::checkForAddOnUpdates($addOns, $errorString);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }  

    public function checkForUrl(&$options = array(), &$errorPhraseKey = '')   
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::checkForUrl($options, $errorPhraseKey);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }   
}
