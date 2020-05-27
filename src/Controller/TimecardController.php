<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Components\Controller\AbstractBaseController;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimecardAddForm;
use Timecard\Form\TimecardForm;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\PaycodeModel;
use Timecard\Model\TimecardModel;
use Exception;

class TimecardController extends AbstractBaseController
{
    public $user_adapter;
    public $employee_adapter;
    
    public function timesheetAction()
    {
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $today = $date->format('Y-m-d');
        
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
        
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getWorkWeek($today);
        } else {
            $work_week = $this->getWorkWeek($this->params()->fromRoute('week', 0));
        }
        
        $view = new ViewModel();
        $timecard = new TimecardModel($this->adapter);
        $paycode = new PaycodeModel($this->adapter);
        
        
        $where = new Where();
        $where->equalTo('WORK_WEEK', $work_week)->AND->equalTo('EMP_UUID', $uuid);
        
        /****************************************
         * RETRIEVE DATA FOR WEEK
         ****************************************/
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from('timecards');
        $select->columns(['UUID','PAY_UUID']);
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return FALSE;
        }
        
        $data = $resultSet->toArray();
        $view->setVariable('data', $data);
        
        /****************************************
         * FORM CREATION
         ****************************************/
        $forms = [];
        
        foreach ($data as $index => $record) {
            $timecard = new TimecardModel($this->adapter);
            $timecard->read(['UUID' => $record['UUID']]);
            $timecard->EMP_UUID = $uuid;
            
            $weekly_timesheet_form = new TimecardForm($timecard->UUID);
            $weekly_timesheet_form->setDbAdapter($this->adapter);
            $weekly_timesheet_form->init();
            
            $weekly_timesheet_form->bind($timecard);
            
            $forms[$record['PAY_UUID']] = $weekly_timesheet_form;
        }
        
        $view->setVariables([
            'timesheet_forms' => $forms,
        ]);
        
        /****************************************
         * ADD PAYCODE SUBFORM
         ****************************************/
        $form = new TimecardAddForm('new-form');
        $form->setDbAdapter($this->adapter);
        $form->init();
        $form->get('EMP_UUID')->setValue($uuid);
        $form->get('WORK_WEEK')->setValue($work_week);
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
         * TIMESHEET SUBMIT SUBFORM
         ****************************************/
//         $form = new TimesheetSubmitForm('timesheet_submit');
//         $form->init();
//         $view->setVariable('timesheet_submit_form', $form);
        
        
        
        /****************************************
         * PROCESS FORMS
         ****************************************/
//         $request = $this->getRequest();
//         if ($request->isPost()) {
//             $data = array_merge_recursive(
//                 $request->getPost()->toArray(),
//                 $request->getFiles()->toArray()
//                 );
//         }
        
        
        
        
        
        $user_entity->getUser($user->UUID);
        $view->setVariable('user_entity', $user_entity);
        
        return $view;
    }
    
    public function addPayCodeAction()
    {
        $view = new ViewModel();
        $view = parent::createAction();
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
        $week = $this->getWorkWeek($data['WORK_WEEK']);
        
        return $this->redirect()->toRoute('timecard/timesheet', ['uuid' => $data['EMP_UUID'], 'week' => $week]);
    }
    
    public function getWorkWeek(String $date)
    {
        $day = date('w', strtotime($date));
        
        return date('Y-m-d', strtotime("$date -$day days"));
//         return date('Y-m-d', strtotime($date));
    }
}