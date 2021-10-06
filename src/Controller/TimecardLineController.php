<?php
namespace Timecard\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\View\Model\ViewModel;

class TimecardLineController extends AbstractBaseController
{
    public function deleteAction()
    {
        $view = new ViewModel();
        $view->setTemplate('base/delete');
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        
        $primary_key = $this->getPrimaryKey();
        $this->model->read([$this->model->getPrimaryKey() => $primary_key]);
        $this->form->bind($this->model);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            $url = $request->getPost('referring_url');
            
            if ($del == 'Yes') {
                $this->model->delete();
            }
            
            return $this->redirect()->toUrl($url);
            
//             $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
//             return $this->redirect()->toRoute($route, ['action' => 'index']);
        }
        
        $view->setVariables([
            'model', $this->model,
            'form' => $this->form,
            'primary_key' => $this->model->getPrimaryKey(),
            'referring_url' => $url,
        ]);
        return ($view);
    }
}