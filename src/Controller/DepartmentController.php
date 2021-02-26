<?php
namespace Timecard\Controller;

use Application\Model\Entity\UserEntity;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate\Like;
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
        $department_preparer = $user_entity->employee;
        
        $sql = new Sql($this->employee_adapter);
        
        $where = new Where();
        $where->equalTo('DEPT', $department_preparer->DEPT)->AND->equalTo('employees.STATUS', $department_preparer::ACTIVE_STATUS);
        
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
            $work_week = $this->getEndofWeek('last week');
            return $this->redirect()->toRoute('dept/timesheet', ['week' => $work_week]);
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
            $where->equalTo('WORK_WEEK', $work_week);
            $where->AND->equalTo('EMP_UUID', $record['UUID']);
            $select->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            $resultSet = new ResultSet($results);
            $resultSet->initialize($results);
            $timecards = $resultSet->toArray();
            
            if (sizeof($timecards)) {
                switch ($timecards[0]['STATUS']) {
                    case $timecard::APPROVED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-primary'>Approved</span>";
                        break;
                    case $timecard::SUBMITTED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-success'>Submitted</span>";
                        break;
                    case $timecard::PREPARERD_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-info'>Prepared</span>";
                        break;
                    case $timecard::COMPLETED_STATUS:
                        $data[$index]['STATUS'] = "<span class='badge badge-secondary'>Completed</span>";
                        break;
                    default:
                        $data[$index]['STATUS'] = "<span class='badge badge-warning'>Pending</span>";
                        break;
                }
                $data[$index]['Timecard'] = $timecards[0]['UUID'];
            } else {
                $data[$index]['STATUS'] = "<span class='badge badge-danger'>Vacant</span>";
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
         * REPORTS SUBTABLE
         ****************************************/
        $reports = [];
        
        $sql = new Sql($this->adapter);
        $select = new Select();
        $select->columns(['UUID', 'NAME'])
        ->from('reports')
        ->where([new Like('NAME', 'DEPT - %')]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $reports = $resultSet->toArray();
        
        $view->setVariable('reports', $reports);
        
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