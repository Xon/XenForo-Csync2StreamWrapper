<?php

class SV_Csync2StreamWrapper_Model_AddOn extends XFCP_SV_Csync2StreamWrapper_Model_AddOn
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
}