<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Hidden;

class TimecardForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'EMP_UUID',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'EMP_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Employee UUID',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'WORK_WEEK',
            'type' => Hidden::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'WORK_WEEK',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Work Week',
                'format' => 'Y-m-d H:m:s',
            ],
        ],['priority' => 100]);
    }
}