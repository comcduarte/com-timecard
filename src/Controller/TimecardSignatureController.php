<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Timecard\Model\TimecardModel;
use Timecard\Model\TimecardSignatureModel;
use Timecard\Model\Entity\TimecardEntity;
use Timecard\Model\TimecardStageModel;

class TimecardSignatureController extends AbstractBaseController
{
    public function activeAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::ACTIVE_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function submitAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        } 
        
        $this->sign($uuid, TimecardModel::SUBMITTED_STATUS);

        return $this->redirect()->toUrl($url);
    }
    
    public function prepareAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::PREPARERD_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function approveAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::APPROVED_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function completeAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::COMPLETED_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function sign($uuid, $status)
    {
        $timecard_entity = new TimecardEntity();
        $timecard_entity->setDbAdapter($this->adapter);
        
        /****************************************
         * GET USER/EMPLOYEE
         ****************************************/
        $user = $this->currentUser();
        
        /****************************************
         * GET TIMECARD
         ****************************************/
        $timecard = new TimecardModel($this->adapter);
        if ($timecard->read(['UUID' => $uuid])) {
            $timecard_entity->TIMECARD_UUID = $timecard->UUID;
            $timecard_entity->EMP_UUID = $timecard->EMP_UUID;
            $timecard_entity->WORK_WEEK = $timecard->WORK_WEEK;
            $timecard_entity->getTimecard();
        } else {
            $this->flashmessenger()->addErrorMessage('Unable to retrieve timecard');
            return;
        }
        
        /****************************************
         * SET TIMECARD STATUS
         ****************************************/
        $timecard->STATUS = $status;
        $timecard->update();
        
        /****************************************
         * GET TIMECARD LINES
         ****************************************/
        foreach ($timecard_entity->TIMECARD_LINES as $index => $line) {
            $line->STATUS = $status;
            $line->update();
        }
        
        /****************************************
         * GET TIMECARD STAGE
         ****************************************/
        $stage = new TimecardStageModel($this->adapter);
        $stage->read(['SEQUENCE' => $status]);
        
        /****************************************
         * SET TIMECARD SIGNATURE
         ****************************************/
        $signature = new TimecardSignatureModel($this->adapter);
        $signature->TIMECARD_UUID = $timecard_entity->TIMECARD_UUID;
        $signature->USER_UUID = $user->UUID;
        $signature->STAGE_UUID = $stage->UUID;
        $signature->create();
    }
}