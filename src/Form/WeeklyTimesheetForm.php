<?php
namespace Timecard\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Text;

class WeeklyTimesheetForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'EMP_UUID',
            'type' => Text::class,
            'attributes' => [
                'id' => 'EMP_UUID',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'EMP_UUID',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'WEEK',
            'type' => Date::class,
            'attributes' => [
                'id' => 'WEEK',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'WEEK',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'SUNDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SUNDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'SUNDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'MONDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'MONDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'MONDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'TUESDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'TUESDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'TUESDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'WEDNESDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'WEDNESDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'WEDNESDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'THURSDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'THURSDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'THURSDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'FRIDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'FRIDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'FRIDAY',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'SATURDAY',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SATURDAY',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'SATURDAY',
            ],
        ],['priority' => 0]);
    }
}