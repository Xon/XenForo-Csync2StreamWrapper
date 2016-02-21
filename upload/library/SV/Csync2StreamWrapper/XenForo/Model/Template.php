<?php

class SV_Csync2StreamWrapper_XenForo_Model_Template extends XFCP_SV_Csync2StreamWrapper_XenForo_Model_Template
{

    public function deleteTemplatesForAddOn($addOnId)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::deleteTemplatesForAddOn($addOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function deleteTemplatesInStyle($styleId)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::deleteTemplatesInStyle($styleId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function importTemplatesAddOnXml(SimpleXMLElement $xml, $addOnId, $maxExecution = 0, $offset = 0)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::importTemplatesAddOnXml($xml, $addOnId, $maxExecution, $offset);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function importTemplatesStyleXml(SimpleXMLElement $xml, $styleId, $addOnId = null )
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::importTemplatesStyleXml($xml, $styleId, $addOnId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function compileAllTemplates($maxExecution = 0, $startStyle = 0, $startTemplate = 0)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::compileAllTemplates($maxExecution, $startStyle, $startTemplate);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }


    public function compileNamedTemplateInStyleTree($title, $styleId)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::compileNamedTemplateInStyleTree($title, $styleId);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function compileMappedTemplatesInStyleTree($templateMapIds)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::compileMappedTemplatesInStyleTree($templateMapIds);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function compileTemplateInStyleTree(array $parsedRecord)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::compileTemplateInStyleTree($parsedRecord);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function writeTemplateFiles($enable = false, $handleOptions = true)
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit([SV_Csync2StreamWrapper_CsyncConfig::getInstance()->www_templates], true);
        try
        {
            return parent::writeTemplateFiles($enable, $handleOptions);
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
}
