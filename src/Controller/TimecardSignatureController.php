<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Components\Controller\AbstractBaseController;
use Employee\Model\DepartmentModel;
use Timecard\Model\TimecardModel;
use Timecard\Model\TimecardSignatureModel;
use Timecard\Model\TimecardStageModel;
use Timecard\Model\Entity\TimecardEntity;
use User\Model\UserModel;

class TimecardSignatureController extends AbstractBaseController
{
    /**
     * 
     * @var \Laminas\Log\Logger $logger
     */
    public $logger;
    public $employee_adapter;
    
    public function activeAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
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
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
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
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::PREPARERD_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function prepareallAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        $work_week = $this->params()->fromRoute('week', 0);
        
        if (! $work_week)  {
            $this->logger->info(sprintf('No Work Week Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Work Week Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $department = new DepartmentModel($this->employee_adapter);
        $department->read(['UUID' => $uuid]);
        $employees = $department->getEmployees();
        
        foreach ($employees as $employee) {
            $timecard_entity = new TimecardEntity();
            $timecard_entity->setDbAdapter($this->adapter);
            $timecard_entity->EMP_UUID = $employee['UUID'];
            $timecard_entity->WORK_WEEK = $work_week;
            if ($timecard_entity->getTimecard()) {
                $this->sign($timecard_entity->TIMECARD_UUID, TimecardModel::PREPARERD_STATUS);
            }
        }
        
        
        
        return $this->redirect()->toUrl($url);
    }
    
    public function approveAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::APPROVED_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function approveallAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        $work_week = $this->params()->fromRoute('week', 0);
        
        if (! $work_week)  {
            $this->logger->info(sprintf('No Work Week Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Work Week Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $department = new DepartmentModel($this->employee_adapter);
        $department->read(['UUID' => $uuid]);
        $employees = $department->getEmployees();
        
        foreach ($employees as $employee) {
            $timecard_entity = new TimecardEntity();
            $timecard_entity->setDbAdapter($this->adapter);
            $timecard_entity->EMP_UUID = $employee['UUID'];
            $timecard_entity->WORK_WEEK = $work_week;
            if ($timecard_entity->getTimecard()) {
                $this->sign($timecard_entity->TIMECARD_UUID, TimecardModel::APPROVED_STATUS);
            }
        }
        
        
        
        return $this->redirect()->toUrl($url);
    }
    
    public function completeAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $this->sign($uuid, TimecardModel::COMPLETED_STATUS);
        
        return $this->redirect()->toUrl($url);
    }
    
    public function completeallAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        if (! $uuid) {
            $this->logger->info(sprintf('No Timecard Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Timecard Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        $work_week = $this->params()->fromRoute('week', 0);
        
        if (! $work_week)  {
            $this->logger->info(sprintf('No Work Week Identifier Specified'));
            $this->flashmessenger()->addErrorMessage('No Work Week Identifier Specified');
            return $this->redirect()->toUrl($url);
        }
        
        $department = new DepartmentModel($this->employee_adapter);
        $department->read(['UUID' => $uuid]);
        $employees = $department->getEmployees();
        
        foreach ($employees as $employee) {
            $timecard_entity = new TimecardEntity();
            $timecard_entity->setDbAdapter($this->adapter);
            $timecard_entity->EMP_UUID = $employee['UUID'];
            $timecard_entity->WORK_WEEK = $work_week;
            if ($timecard_entity->getTimecard()) {
                $this->sign($timecard_entity->TIMECARD_UUID, TimecardModel::COMPLETED_STATUS);
            }
        }
        
        
        
        return $this->redirect()->toUrl($url);
    }
    
    public function sign($uuid, $status)
    {
        $timecard_entity = new TimecardEntity();
        $timecard_entity->setDbAdapter($this->adapter);
        
        /****************************************
         * GET CURRENT USER/EMPLOYEE
         * 
         * @var UserModel $user
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
            $this->logger->info(sprintf('[%s] Unable to retrieve timecard [%s]', $user->USERNAME, $uuid));
            $this->flashmessenger()->addErrorMessage('Unable to retrieve timecard');
            return;
        }
        
        /****************************************
         * GET USER
         ****************************************/
        $user_entity = new UserEntity($this->employee_adapter);
        $user_entity->getEmployee($timecard_entity->EMP_UUID);
        
        /****************************************
         * SET TIMECARD STATUS
         ****************************************/
        $timecard_entity->STATUS = $status;
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
        
        /****************************************
         * Logging
         ****************************************/
        $this->logger->info(sprintf('%s signed %s with status %s', $user->USERNAME, $uuid, TimecardModel::retrieveStatus($status)));
        
        /****************************************
         * Notifications
         ****************************************/
        $this->getEventManager()->trigger(TimecardModel::EVENT_SUBMITTED, $this, ['timecard_entity' => $timecard_entity]);
    }
}