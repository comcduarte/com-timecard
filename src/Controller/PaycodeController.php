<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\ViewModel;
use Exception;

class PaycodeController extends AbstractBaseController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('base/index');
        
        /**
         * Override fetchall 
         */
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->model->getTableName());
        $select->columns(['CODE','DESC','RESOURCE']);
        $select->where(new Where());
        $select->order('RESOURCE DESC','CODE');
        
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
        
        $view->setvariables ([
            'data' => $records,
            'header' => $header,
            'primary_key' => $this->model->getPrimaryKey(),
        ]);
        return $view;
    }
}