<?php
namespace Timecard\Controller;

use Annotation\Traits\AnnotationAwareTrait;
use Application\Model\Entity\UserEntity;
use Components\Controller\AbstractBaseController;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimecardLineForm;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\TimecardLineModel;
use Timecard\Model\TimecardStageModel;
use Timecard\Model\Entity\TimecardEntity;
use Timecard\Traits\DateAwareTrait;
use User\Model\UserModel;
use Components\Traits\AclAwareTrait;

class TimecardController extends AbstractBaseController
{
    use DateAwareTrait;
    use AnnotationAwareTrait;
    use AclAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    public $timecard_add_form;
    
    public function timesheetAction()
    {
        $view = new ViewModel();
        
        /****************************************
         * GET USER/EMPLOYEE
         ****************************************/
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        
        $user = $this->currentUser();
        
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (! $uuid) {
            $user_entity->getUser($user->UUID);
            $uuid = $user_entity->employee->UUID;
        } else {
            $user_entity->getEmployee($uuid);
        }
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek();
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        
        
        /****************************************
         * GET TIMECARD
         ****************************************/
        $timecard = new TimecardEntity();
        $timecard->setDbAdapter($this->adapter);
        
        $timecard->WORK_WEEK = $work_week;
        $timecard->EMP_UUID = $user_entity->employee->UUID;
        $timecard->getTimecard();
        $view->setVariable('timecard_uuid', $timecard->TIMECARD_UUID);
        $view->setVariable('HOURS', $timecard->HOURS);
        
        /****************************************
         * FORM CREATION
         ****************************************/
        $forms = [];
        
        foreach ($timecard->TIMECARD_LINES as $index => $timecard_line) {
            $timecard_line_form = new TimecardLineForm();
            $timecard_line_form->setDbAdapter($this->adapter);
            $timecard_line_form->init();
            $timecard_line_form->bind($timecard_line);
            $forms[$index] = $timecard_line_form;
        }
        
        ksort($forms);
        
        $view->setVariable('timesheet_forms', $forms);
        
        /****************************************
         * TIMECARD SIGNATURES
         ****************************************/
        $data = [];
        foreach ($timecard->TIMECARD_SIGNATURES as $signature) {
            $stage = new TimecardStageModel($this->adapter);
            $stage->read(['UUID' => $signature->STAGE_UUID]);
            
            $sign_user = new UserModel($this->adapter);
            $sign_user->read(['UUID' => $signature->USER_UUID]);
            
            $record = [
                'User' => $sign_user->USERNAME,
                'Stage' => $stage->NAME,
                'Timestamp' => $signature->DATE_CREATED,
            ];
            
            $data[] = $record;  
        }
        $view->setVariable('timecard_signatures' , $data);
        
        /****************************************
         * ADD PAYCODE SUBFORM
         ****************************************/
        $form = $this->timecard_add_form;
//         $form->setDbAdapter($this->adapter);
//         $form->init();
        
        /** Create custom SQL object to populate dropdown. **/
//         $select = new Select();
//         $select->from('time_cards_stages');
//         $select->columns(['UUID','NAME']);
//         $select->order(['NAME']);
        
        /** Retrieve Database Select Object **/
        $form->get('PAY_UUID')->roles = $user->memberOf();
        $form->get('PAY_UUID')->setAclService($this->acl_service)->populateElement();
        
        /** END Custom SQL **/
        
        $form->get('TIMECARD_UUID')->setValue($timecard->TIMECARD_UUID);
        $view->setVariable('timecard_add_form', $form);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        $form->get('EMP_UUID')->setValue($uuid);
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * ANNOTATIONS
         ****************************************/
        $this->annotations_tablename = $this->model->getTableName();
        $this->annotations_prikey = $timecard->TIMECARD_UUID;
        $this->annotations_user = $user->UUID;
        $view->setVariables($this->getAnnotations());
        
        $view->setVariable('user', $user);
        $view->setVariable('user_entity', $user_entity);
        
        /****************************************
         * ACL
         ****************************************/
        $view->setVariable('acl_service', $this->acl_service);
        
        return $view;
    }
    
    public function addPayCodeAction()
    {
        $form = new TimecardLineForm();
        $form->setDbAdapter($this->adapter);
        $form->init();
        $model = new TimecardLineModel($this->adapter);
        
        $request = $this->getRequest();
        $form->bind($model);
        
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $form->setData($post);
            
            if ($form->isValid()) {
                $model->create();
                
                $this->flashmessenger()->addSuccessMessage('Add New Record Successful');
            } else {
                foreach ($this->form->getMessages() as $message) {
                    if (is_array($message)) {
                        $message = array_pop($message);
                    }
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function filterAction()
    {
        $form = new TimesheetFilterForm();
        $form->init();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
        }
        $week = $this->getEndofWeek($data['WORK_WEEK']);
        
        return $this->redirect()->toRoute('timecard/timesheet', ['uuid' => $data['EMP_UUID'], 'week' => $week]);
    }
}