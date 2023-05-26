<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Text;

class TimecardSignatureForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'TIMECARD_UUID',
            'type' => Text::class,
            'attributes' => [
                'id' => 'TIMECARD_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Time Card',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'USER_UUID',
            'type' => Text::class,
            'attributes' => [
                'id' => 'USER_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'User',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'STAGE_UUID',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'STAGE_UUID',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Stage',
            ],
        ],['priority' => 100]);
    }
}