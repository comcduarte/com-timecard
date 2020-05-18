<?php
namespace Timecard\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Date;

class TimesheetFilterForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'WEEK',
            'type' => Date::class,
            'attributes' => [
                'id' => 'WEEK',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Week Ending',
            ],
        ],['priority' => 0]);
    }
}