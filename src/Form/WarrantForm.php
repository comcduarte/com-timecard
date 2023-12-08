<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\Form\Element\Text;

class WarrantForm extends AbstractBaseForm
{
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'WARRANT_NUM',
            'type' => Text::class,
            'attributes' => [
                'id' => 'WARRANT_NUM',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Warrant Number',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'WORK_WEEK',
            'type' => DateTimeLocal::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'WORK_WEEK',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Work Week',
            ],
        ],['priority' => 100]);
    }
}