<?php
namespace Timecard\Form;

use Components\Form\Element\AclDatabaseSelect;
use Components\Form\Element\Uuid;
use Components\Traits\AclAwareTrait;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;

class TimecardAddForm extends Form
{
    use AdapterAwareTrait;
    use AclAwareTrait;
    
    public function init()
    {
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
            'name' => 'TIMECARD_UUID',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'TIMECARD_UUID',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'TIMECARD UUID',
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
            'type' => AclDatabaseSelect::class,
            'attributes' => [
                'id' => 'PAY_UUID',
                'class' => 'form-select',
            ],
            'options' => [
                'label' => 'Pay Code',
                'empty_option' => 'Select a Paycode',
                'acl_service' => $this->getAclService(),
                'acl_resource_column' => 'RESOURCE',
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
            'name' => 'ORD',
            'type' => Text::class,
            'attributes' => [
                'id' => 'ORD',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'ORDER',
            ],
        ],['priority' => 100]);
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Add Paycode',
                'class' => 'btn btn-primary form-control',
                'id' => 'TIMECARD_ADD_SUBMIT',
            ],
        ],['priority' => 0]);
    }
}