<?php

class SV_Csync2StreamWrapper_Patches_XenForo_Model_Template extends XFCP_SV_Csync2StreamWrapper_Patches_XenForo_Model_Template
{
    protected function _insertCompiledTemplateRecord($styleId, $languageId, $title, $compiledTemplate)
    {
        // ensure that only changed template files are written out to save cache invalidations
        $options = XenForo_Application::get('options');
        $templateFiles = $options->templateFiles;
        if ($templateFiles)
        {
            $filename = XenForo_Template_FileHandler::get($title, $styleId, $languageId);
            $oldContents = @file_get_contents($filename);
            if ($oldContents !== false)
            {
                // XenForo_Template_FileHandler::save
                $newContents = '<?php if (!class_exists(\'XenForo_Application\', false)) die(); ' . $compiledTemplate;
                if ($oldContents === $newContents)
                {
                    $options->set('templateFiles', false);
                }
            }
        }

        parent::_insertCompiledTemplateRecord($styleId, $languageId, $title, $compiledTemplate);

        if ($templateFiles)
        {
            $options->set('templateFiles', true);
        }
    }
}
