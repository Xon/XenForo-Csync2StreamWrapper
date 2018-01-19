<?php

class SV_Csync2StreamWrapper_XenForo_Model_AddOn extends XFCP_SV_Csync2StreamWrapper_XenForo_Model_AddOn
{
    public function extractZip($fileName, $baseDir = 'install/addons', $installId = null)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code], true);
        try
        {
            return parent::extractZip($fileName, $baseDir, $installId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function deleteAll($directory, $empty = false)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code], true);
        try
        {
            return parent::deleteAll($directory, $empty);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    protected function _recursiveCopy(AddOnInstaller_Model_Deployment_Abstract $deployer, $source, $destination, array &$failedFiles)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code], true);
        try
        {
            return parent::_recursiveCopy($deployer, $source, $destination, $failedFiles);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function recursiveCopy($source, $destination)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code], true);
        try
        {
            /** @noinspection PhpUndefinedMethodInspection */
            return parent::recursiveCopy($source, $destination);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function installAddOnXmlFromFile($fileName, $upgradeAddOnId = false)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code], true);
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
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code, SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
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
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code, SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
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
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code, SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
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
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code, SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
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
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_code, SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
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

if (false)
{
    class XFCP_SV_Csync2StreamWrapper_XenForo_Model_AddOn extends AddOnInstaller_XenForo_Model_AddOn {}
}
