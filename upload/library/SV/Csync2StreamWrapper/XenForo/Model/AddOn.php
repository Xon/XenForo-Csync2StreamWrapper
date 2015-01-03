<?php

class SV_Csync2StreamWrapper_XenForo_Model_AddOn extends XFCP_SV_Csync2StreamWrapper_XenForo_Model_AddOn
{

    public function installAddOnXmlFromFile($fileName, $upgradeAddOnId = false)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::installAddOnXmlFromFile($fileName, $upgradeAddOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
    
    public function installAddOnXml(SimpleXMLElement $xml, $upgradeAddOnId = false)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::installAddOnXml($xml, $upgradeAddOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
    
    public function importAddOnExtraDataFromXml(SimpleXMLElement $xml, $addOnId)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::importAddOnExtraDataFromXml($xml, $addOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
    
	public function deleteAddOnMasterData($addOnId)
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::deleteAddOnMasterData($addOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }    
    }    
    
	public function rebuildAddOnCaches()
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::rebuildAddOnCaches();
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }    
    }    

	public function rebuildAddOnCachesAfterActiveSwitch(array $addon)
	{
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::rebuildAddOnCachesAfterActiveSwitch($addon);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }    
    }        
}