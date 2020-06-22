<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Timecard\Model\TimecardModel;
use Timecard\Model\TimecardSignatureModel;
use Timecard\Model\Entity\TimecardEntity;

class TimecardSignatureController extends AbstractBaseController
{
    public function submitAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        $timecard_entity = new TimecardEntity();
        $timecard_entity->setDbAdapter($this->adapter);
        
        if (! $uuid) {
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        } 
        
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
            return $this->redirect()->toUrl($url);
        }
        
        /****************************************
         * SET TIMECARD STATUS
         ****************************************/
        $timecard->STATUS = $timecard::SUBMITTED_STATUS;
        $timecard->update();
        
        /****************************************
         * GET TIMECARD LINES
         ****************************************/
        foreach ($timecard_entity->TIMECARD_LINES as $index => $line) {
            $line->STATUS = $line::SUBMITTED_STATUS;
            $line->update();
        }
        
        /****************************************
         * SET TIMECARD SIGNATURE
         ****************************************/
        $signature = new TimecardSignatureModel($this->adapter);
        $signature->TIMECARD_UUID = $timecard_entity->TIMECARD_UUID;
        $signature->USER_UUID = $user->UUID;
        $signature->STAGE_UUID = '93303e78-a88f-8464-05c0-683e3142836c';
        $signature->create();

        return $this->redirect()->toUrl($url);
    }
    
    public function prepareAction()
    {
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function approveAction()
    {
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}