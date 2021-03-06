<?php

class SV_Csync2StreamWrapper_XenForo_Model_Sitemap extends XFCP_SV_Csync2StreamWrapper_XenForo_Model_Sitemap
{

    public function cleanUpOldSitemaps($skipId = null)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_data]);
        try
        {
            return parent::cleanUpOldSitemaps($skipId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }


    public function cleanUpSitemap(array $sitemap)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_data]);
        try
        {
            return parent::cleanUpSitemap($sitemap);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function compressSitemapFile($setId, $counter)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_data]);
        try
        {
            return parent::compressSitemapFile($setId, $counter);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
}

if (false)
{
    class XFCP_SV_Csync2StreamWrapper_XenForo_Model_Sitemap extends XenForo_Model_Sitemap {}
}
