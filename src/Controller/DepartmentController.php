<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\TimecardModel;
use Timecard\Traits\DateAwareTrait;

class DepartmentController extends AbstractActionController
{
    use AdapterAwareTrait;
    use DateAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    public $timecard_adapter;
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        $user = $this->currentUser();
        $user_entity = new UserEntity($this->user_adapter);
        $user_entity->employee->setDbAdapter($this->employee_adapter);
        $user_entity->department->setDbAdapter($this->employee_adapter);
        $user_entity->getUser($user->UUID);
        $view->setVariable('dept', $user_entity->department);
        $view->setVariable('role', $user_entity->groups[0]['ROLENAME']);
        
        /****************************************
         * RETRIEVE DEPARTMENT EMPLOYEES
         ****************************************/
        $departmet_preparer = $user_entity->employee;
        
        $sql = new Sql($this->employee_adapter);
        
        $where = new Where();
        $where->equalTo('DEPT', $departmet_preparer->DEPT)->AND->equalTo('employees.STATUS', $departmet_preparer::ACTIVE_STATUS);
        
        $select = new Select();
        $select->from('employees');
        $select->where($where);
        $select->columns([
            'UUID' => 'UUID',
            'First Name' => 'FNAME',
            'Last Name' => 'LNAME',
            'Email' => 'EMAIL',
        ]);
        $select->order('LNAME ASC');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $data = $resultSet->toArray();
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek();
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
        /****************************************
         * RETRIEVE EMPLOYEE SUBMISSION STATUS
         ****************************************/
        $timecard = new TimecardModel($this->timecard_adapter);
        
        foreach ($data as $index => $record) {
            $timecards = [];
            $sql = new Sql($this->user_adapter);
            $select = new Select();
            $select->from($timecard->getTableName());
            $where = new Where();
            $where->equalTo('STATUS', 1)->AND->equalTo('WORK_WEEK', $work_week);
            $where->AND->equalTo('EMP_UUID', $record['UUID']);
            $select->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            $resultSet = new ResultSet($results);
            $resultSet->initialize($results);
            $timecards = $resultSet->toArray();
            
            if (sizeof($timecards)) {
                $data[$index]['STATUS'] = 'Pending';
            } else {
                $data[$index]['STATUS'] = 'Submitted';
            }
        }
        
        
        $view->setVariable('employees', $data);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * SET MISCELLANEOUS VARIABLES
         ****************************************/
        
        return $view;
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
        
        return $this->redirect()->toRoute('dept/timesheet', ['week' => $week]);
    }
}