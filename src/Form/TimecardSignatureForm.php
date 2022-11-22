<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Db\Adapter\AdapterAwareTrait;

class TimecardSignatureForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'TIMECARD_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'id' => 'TIMECARD_UUID',
                'class' => 'form-select',
            ],
            'options' => [
                'label' => 'Time Card',
                'database_adapter' => $this->adapter,
                'database_table' => 'time_cards',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'WORK_WEEK',
                    'EMP_UUID',
                ],
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'USER_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'id' => 'USER_UUID',
                'class' => 'form-select',
            ],
            'options' => [
                'label' => 'User',
                'database_adapter' => $this->adapter,
                'database_table' => 'users',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'LNAME',
                    'FNAME',
                ],
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'STAGE_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-select',
                'id' => 'STAGE_UUID',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Stage',
                'database_adapter' => $this->adapter,
                'database_table' => 'time_cards_stages',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'NAME',
                    'SEQUENCE',
                ],
            ],
        ],['priority' => 100]);
    }
}