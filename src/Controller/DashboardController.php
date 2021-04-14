<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Employee\Model\DepartmentModel;
use Employee\Model\EmployeeModel;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate\Like;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\TimecardModel;
use Timecard\Traits\DateAwareTrait;

class DashboardController extends AbstractBaseController
{
    use AdapterAwareTrait;
    use DateAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    public $timecard_adapter;
    
    private function getWorkWeek() 
    {
        /****************************************
         * GET WORK WEEK
         ****************************************/
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $work_week = $this->getEndofWeek($data['WORK_WEEK']);
        } elseif (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            return $this->redirect()->toRoute('dashboard/default', ['week' => $work_week], [], TRUE);
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        
        return $work_week;
    }
    
    public function payrollAction()
    {
        global $work_week;
        
        $view = new ViewModel();
        $user = $this->currentUser();
        $data = [];
        $timecard = new TimecardModel($this->timecard_adapter);
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
            return $this->redirect()->toRoute('dashboard/default', ['action' => 'payroll', 'week' => $work_week], [], TRUE);
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        $form = new TimesheetFilterForm();
        $form->init();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            return $this->redirect()->toRoute('dashboard/default', ['action' => 'payroll', 'week' => $this->getEndofWeek($data['WORK_WEEK'])]);
        }
        
        $form->get('WORK_WEEK')->setValue($work_week);
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        /****************************************
         * RETRIEVE DEPARTMENTS
         ****************************************/
        $department_model = new DepartmentModel($this->employee_adapter);
        $where = new Where();
//         $where->equalTo('CODE', '03500');
        $where->equalTo('STATUS', $department_model::ACTIVE_STATUS);
        $data = $department_model->fetchAll($where, ['CODE']);
        
        /****************************************
         * RETRIEVE TIMECARDS PER DEPARTMENT
         ****************************************/
        $sql = new Sql($this->employee_adapter);
        foreach ($data as $index => $department) {
            unset ($data[$index]['DATE_CREATED']);
            unset ($data[$index]['DATE_MODIFIED']);
            
            $where = new Where();
            $where->equalTo('DEPT', $department['UUID'])->AND->equalTo('employees.STATUS', EmployeeModel::ACTIVE_STATUS);
            
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
            $employees = $resultSet->toArray();
            
            $data[$index]['STATUS'] = 100;
            foreach ($employees as $employee) {
                if ($timecard->read(['EMP_UUID' => $employee['UUID'], 'WORK_WEEK' => $work_week])) {
                    if ($timecard->STATUS < $data[$index]['STATUS']) {
                        $data[$index]['STATUS'] = $timecard->STATUS;
                    }
                }
            }
            $data[$index]['STATUS'] = $timecard->formatStatus($data[$index]['STATUS']);
        }
        
        unset($department_model);
        
        $view->setVariable('data', $data);
        
        /****************************************
         * TIMESHEET FILTER SUBFORM
         ****************************************/
        
        
        /****************************************
         * REPORTS SUBTABLE
         ****************************************/
        $reports = [];
        
        $sql = new Sql($this->adapter);
        $select = new Select();
        $select->columns(['UUID', 'NAME'])
        ->from('reports')
        ->where([new Like('NAME', 'PY - %')]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $results = $statement->execute();
        $resultSet = new ResultSet($results);
        $resultSet->initialize($results);
        $reports = $resultSet->toArray();
        
        $view->setVariable('reports', $reports);
        
        return $view;
    }
    
    public function deptAction()
    {
        $view = new ViewModel();
        
        
        
        
        /****************************************
         * GET WORK WEEK
         ****************************************/
        if (! $this->params()->fromRoute('week', 0)) {
            $work_week = $this->getEndofWeek('last week');
//             return $this->redirect()->toRoute('dept/timesheet', ['week' => $work_week]);
        } else {
            $work_week = $this->getEndofWeek($this->params()->fromRoute('week', 0));
        }
        $view->setVariable('work_week', $work_week);
        
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
         * RETRIEVE DEPARTMENT EMPLOYEES
         ****************************************/
        $dept = '';
        if (! $this->params()->fromRoute('uuid', 0)) {
            
        } else {
            $dept = $this->params()->fromRoute('uuid', 0);
        }
        $sql = new Sql($this->employee_adapter);
        
        $where = new Where();
        $where->equalTo('DEPT', $dept)->AND->equalTo('employees.STATUS', EmployeeModel::ACTIVE_STATUS);
        
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
         * GET DEPARTMENT
         ****************************************/
        $department = new DepartmentModel($this->employee_adapter);
        $department->read(['UUID' => $dept]);
        $view->setVariable('dept', $department);
        
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
        
        return $view;
    }
}