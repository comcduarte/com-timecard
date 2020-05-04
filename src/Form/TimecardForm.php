<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Text;

class TimecardForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'EMP_UUID',
            'type' => Text::class,
            'attributes' => [
                'id' => 'EMP_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Employee UUID',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'WORK_DATE',
            'type' => Date::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'WORK_DATE',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Work Date',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PAY_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'id' => 'PAY_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Pay Code',
                'database_adapter' => $this->adapter,
                'database_table' => 'time_pay_codes',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'CODE',
                    'DESC',
                ],
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'HOURS',
            'type' => Text::class,
            'attributes' => [
                'id' => 'HOURS',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Hours',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DAYS',
            'type' => Text::class,
            'attributes' => [
                'id' => 'DAYS',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Days',
            ],
        ],['priority' => 100]);
    }
}