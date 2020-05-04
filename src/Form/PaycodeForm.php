<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\Text;

class PaycodeForm extends AbstractBaseForm
{
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'CODE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'CODE',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Pay Code',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DESC',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DESC',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Pay Code Description',
            ],
        ],['priority' => 100]);
    }
}