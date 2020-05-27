<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;

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
        
        $days = ['SUN','MON','TUES','WED','THURS','FRI','SAT', 'DAYS'];
        foreach ($days as $day) { 
            $this->add([
                'name' => $day,
                'type' => Text::class,
                'attributes' => [
                    'id' => $day,
                    'class' => 'form-control',
                    'onchange' => 'this.form.submit()',
                ],
                'options' => [
                    'label' => $day,
                ],
            ],['priority' => 100]);
        }
        
        $this->add([
            'name' => 'STATUS',
            'type' => Checkbox::class,
            'attributes' => [
                'class' => 'checkbox checkbox-slider--b-flat',
                'onchange' => 'this.form.submit()',
            ],
            'options' => [
                'label' => 'Submit Timesheet',
                'checked_value' => 2,
                'unchecked_value' => 1,
            ],
        ]);
        
        $submit = $this->get('SUBMIT');
        $submit->setAttribute('id', '_SUBMIT_');
    }
}