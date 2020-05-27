<?php
namespace Timecard\Form;

use Components\Form\Element\HiddenSubmit;
use Components\Form\Element\Uuid;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Checkbox;

class TimesheetSubmitForm extends Form
{
    public function init()
    {
        $this->setAttribute('action', 'timecard/timesheet');
        
        $this->add([
            'name' => 'UUID',
            'type' => Uuid::class,
            'attributes' => [
                'id' => 'UUID',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'UUID',
            ],
        ],['priority' => 0]);
        
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
    }
}