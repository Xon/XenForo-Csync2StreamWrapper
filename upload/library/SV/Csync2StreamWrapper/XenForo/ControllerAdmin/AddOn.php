<?php

class SV_Csync2StreamWrapper_XenForo_ControllerAdmin_AddOn extends XFCP_SV_Csync2StreamWrapper_XenForo_ControllerAdmin_AddOn
{
    public function actionStepUpload()
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::actionStepUpload();
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function actionStepExtract()
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::actionStepExtract();
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }

    public function actionStepDeploy()
    {
        SV_Csync2StreamWrapper_csyncwrapper::DeferrCommit(true);
        try
        {
            return parent::actionStepDeploy();
        }
        finally
        {
            SV_Csync2StreamWrapper_csyncwrapper::FinalizeCommit();
        }
    }
}