<?php
namespace Timecard\Controller;

use Annotation\Traits\AnnotationAwareTrait;
use Application\ActionMenu\ActionMenu;
use Application\Model\Entity\UserEntity;
use Components\Controller\AbstractBaseController;
use Components\Traits\AclAwareTrait;
use Help\Model\HelpModel;
use Laminas\Box\API\AccessTokenAwareTrait;
use Laminas\Box\API\MetadataQuery;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Html\Hyperlink;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Leave\Model\LeaveModel;
use Settings\Model\SettingsModel;
use Timecard\Form\TimecardAddForm;
use Timecard\Form\TimecardLineForm;
use Timecard\Form\TimesheetFilterForm;
use Timecard\Model\TimecardLineModel;
use Timecard\Model\TimecardStageModel;
use Timecard\Model\Entity\TimecardEntity;
use Timecard\Traits\DateAwareTrait;
use User\Model\UserModel;
use DateTime;
use Laminas\Box\API\Resource\ClientError;
use Laminas\Box\API\Exception\ClientErrorException;

class TimecardController extends AbstractBaseController
{
    use DateAwareTrait;
    use AnnotationAwareTrait;
    use AclAwareTrait;
    use AccessTokenAwareTrait;
    
    public $user_adapter;
    public $employee_adapter;
    
    /**
     * @var TimecardAddForm
     */
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
        
        /****************************************
         * GET UUID
         ****************************************/
        $request = $this->getRequest();
        $post_uuid = NULL;
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $post_uuid = $data['UUID'];
            
            $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
            $params = array_merge(
                $this->getEvent()->getRouteMatch()->getParams(),
                ['uuid' => $post_uuid]
                );
            
            return $this->redirect()->toRoute($route, $params);
        }
        
        /**
         * @var FlashMessenger $flashMessenger
         */
        $uuid = $this->params()->fromRoute('uuid', $post_uuid);
        if (! $uuid) {
            $retval = $user_entity->getUser($user->UUID);
            if (! $retval) {
                $this->flashMessenger()->addErrorMessage('Unable to find employee.');
                return $this->redirect()->toRoute('home');
            }
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
        if (!$timecard->getTimecard()) {
            $timecard->createTimecard();
            $timecard->getTimecard();
        }
        $view->setVariable('timecard_uuid', $timecard->TIMECARD_UUID);
        $view->setVariable('HOURS', $timecard->HOURS);
        
        /****************************************
         * FILES\ATTACHMENTS
         ****************************************/
        $settings = new SettingsModel($this->adapter);
        $settings->read(['MODULE' => 'BOX', 'SETTING' => 'APP_FOLDER_ID']);
        $app_folder_id = $settings->VALUE;
        
        /**
         *  
         * @var \Laminas\Box\API\MetadataQuery $metadata_query
         */
        $metadata_query = new MetadataQuery($this->access_token);
        $metadata_query_search_results = $metadata_query->metadata_query(
            (string) $app_folder_id,
            sprintf('%s_%s.%s', $this->getAccessToken()->box_subject_type, $this->getAccessToken()->box_subject_id, 'webappReference'),
            "referenceUuid = :uuid",
            [
                'uuid' => $timecard->TIMECARD_UUID,
            ],
            );
        
        if ($metadata_query_search_results instanceof ClientError) {
            throw new ClientErrorException($metadata_query_search_results->message);
        }
        
        $view->setVariables([
            'files' => $metadata_query_search_results->entries,
            'files_title' => 'Related Files',
            'files_reference' => $timecard->TIMECARD_UUID,
        ]);
        
        /**
         * ActionMenu
         * 
         */
        $actionMenu = new ActionMenu();
        
        $update = new Hyperlink();
        $update->class = 'dropdown-item';
        $update->data_url_route = 'box';
        $update->data_url_params = ['action' => 'view'];
        $update->data_href_param = 'id';
        $update->target = "_blank";
        $update->setLabel('View');
        
        $delete = new Hyperlink();
        $delete->class = 'dropdown-item bg-danger text-white';
        $delete->data_url_route = 'home';
        $delete->data_url_params = ['action' => 'delete'];
        $delete->data_href_param = 'id';
        $delete->setLabel('Delete');
        
        $actionMenu->add_menu_item($update);
//         $actionMenu->add_menu_item($delete);
        
        $view->setVariable('actionMenu', $actionMenu);
        
        /****************************************
         * FORM CREATION
         ****************************************/
        $forms = [];
        
        foreach ($timecard->TIMECARD_LINES as $index => $timecard_line) {
            $timecard_line_form = new TimecardLineForm();
            $timecard_line_form->setDbAdapter($this->adapter);
            $timecard_line_form->init();
            $timecard_line_form->bind($timecard_line);
            
            $timecard_line_form->get('PAY_UUID')->roles = $user->memberOf();
            $timecard_line_form->get('PAY_UUID')->setAclService($this->acl_service)->populateElement();
            
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
            
            $created = new DateTime($signature->DATE_CREATED, new \DateTimeZone('UTC'));
            $created->setTimezone(new \DateTimeZone('America/New_York'));
            
            $record = [
                'User' => $sign_user->USERNAME,
                'Stage' => $stage->NAME,
                'Timestamp' => $created->format('Y-m-d H:i:s'),
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
         * LEAVE
         ****************************************/
        $leave = new LeaveModel($this->adapter);
        $where = new Where();
        $where->equalTo('EMP_NUM', $user_entity->employee->EMP_NUM)->and->equalTo('STATUS', $leave::ACTIVE_STATUS);
        $select = new Select();
        $select->columns([
            'CODE','BEGIN','ACCRUAL','TAKEN','FORFEIT','PAID','BALANCE',
        ]);
        $leave->setSelect($select);
        $leave_totals = $leave->fetchAll($where);
        $view->setVariable('leave_totals', $leave_totals);
        
        /****************************************
         * HELP
         ****************************************/
        $help = new HelpModel($this->adapter);
        $help->read(['NUMBER' => '0x80000001']);
        $view->setVariable('help_leave', $help->getArrayCopy());
        
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
            
            $form->setInputFilter($model->getInputFilter());
            $form->setData($post);
            
            if ($form->isValid()) {
                $model->create();
                
                $this->flashmessenger()->addSuccessMessage('Add New Record Successful');
            } else {
                foreach ($form->getMessages() as $message) {
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