<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Sql\Select;

class WarrantController extends AbstractBaseController
{
    public function indexAction()
    {
        $view = new ViewModel();
        
        
        $select = new Select();
        $select->columns([
            'UUID' => 'UUID',
            'Warrant' => 'WARRANT_NUM',
            'Week Ending' => 'WORK_WEEK',
        ]);
        $select->limit(20)->order('WORK_WEEK ' . $select::ORDER_DESCENDING);
        $this->model->setSelect($select);
        
        $view = parent::indexAction();
        
        return $view;
    }
}