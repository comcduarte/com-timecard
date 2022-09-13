<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\View\Model\ViewModel;

class TimecardStageController extends AbstractBaseController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view = parent::indexAction();
        $view->setTemplate('base/subtable');
        
        $params = [
            [
                'route' => 'timecard/stages',
                'action' => 'update',
                'key' => 'UUID',
                'label' => 'Update',
            ],
            [
                'route' => 'timecard/stages',
                'action' => 'delete',
                'key' => 'UUID',
                'label' => 'Delete',
            ],
        ];
        
        $view->setVariables([
            'title' => 'Timecard Stages',
            'params' => $params,
            'search' => true,
        ]);
        return $view;
    }
}