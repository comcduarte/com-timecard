<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\ViewModel;
use Exception;

class ShiftCodeController extends AbstractBaseController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view = parent::indexAction();
        $view->setTemplate('base/subtable');
        
        /**
         * Override FetchAll
         */
        
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->model->getTableName());
        $select->columns(['UUID','CODE','DESC','HOUR']);
        $select->where(new Where());
        $select->order('HOUR DESC','CODE');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return FALSE;
        }
        
        $records = $resultSet->toArray();
        $header = [];
        
        if (!empty($records)) {
            $header = array_keys($records[0]);
        }
        
        
        $params = [
            [
                'route' => 'shiftcode/default',
                'action' => 'update',
                'key' => 'UUID',
                'label' => 'Update',
            ],
            [
                'route' => 'shiftcode/default',
                'action' => 'delete',
                'key' => 'UUID',
                'label' => 'Delete',
            ],
        ];
        
        $view->setVariables([
            'data' => $records,
            'header' => $header,
            'title' => 'Shift Codes',
            'params' => $params,
            'search' => true,
        ]);
        return $view;
    }
}