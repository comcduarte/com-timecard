<?php
namespace Timecard\Form;

use Components\Form\Element\HiddenSubmit;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Hidden;

class TimesheetFilterForm extends Form
{
    public function init()
    {
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
            'type' => Date::class,
            'attributes' => [
                'id' => 'WORKWEEK',
                'class' => 'form-control',
                'required' => 'true',
                'onchange' => 'this.form.submit()',
            ],
            'options' => [
                'label' => 'Week Ending',
            ],
        ],['priority' => 0]);
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => HiddenSubmit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
        
        $submit = $this->get('SUBMIT');
        $submit->setAttribute('id', '_SUBMIT_');
    }
}