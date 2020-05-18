<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\ViewModel;
use Timecard\Form\TimecardForm;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\PaycodeModel;
use Timecard\Model\TimecardModel;
use Exception;

class TimecardController extends AbstractBaseController
{
    public function timesheetAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (! $uuid) {
            $user = $this->currentUser();
            $uuid = $user->UUID;
        }
        
        $view = new ViewModel();
        $timecard = new TimecardModel($this->adapter);
        $paycode = new PaycodeModel($this->adapter);
        
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $today = $date->format('Y-m-d 00:00:00');
        $where = new Where();
        
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
        
               
        
        
        
        $form = new TimesheetFilterForm();
        $form->init();
        $view->setVariables([
            'week_form' => $form,
        ]);
        
        
        
        
        
        /****************************************
         * PROCESS FORMS
         ****************************************/
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
        }
        
        
        
        
        
        
        
        
        return $view;
    }

}