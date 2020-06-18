<?php
namespace Timecard\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Form\Element\Text;
use Laminas\Db\Adapter\AdapterAwareTrait;

class TimecardStageForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'NAME',
            'type' => Text::class,
            'attributes' => [
                'id' => 'NAME',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Stage Name',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'SEQUENCE',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SEQUENCE',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Stage Sequence',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PARENT',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'id' => 'PARENT',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Parent Stage',
                'database_adapter' => $this->adapter,
                'database_table' => 'time_cards_stages',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'UUID',
                    'NAME',
                    'PARENT',
                ],
            ],
        ],['priority' => 100]);
    }
}