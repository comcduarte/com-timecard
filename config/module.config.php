<?php
namespace Timecard;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Timecard\Controller\PaycodeController;
use Timecard\Controller\TimecardConfigController;
use Timecard\Controller\TimecardController;
use Timecard\Controller\Factory\PaycodeControllerFactory;
use Timecard\Controller\Factory\TimecardConfigControllerFactory;
use Timecard\Controller\Factory\TimecardControllerFactory;
use Timecard\Form\PaycodeForm;
use Timecard\Form\TimecardForm;
use Timecard\Form\Factory\PaycodeFormFactory;
use Timecard\Form\Factory\TimecardFormFactory;
use Timecard\Service\Factory\TimecardModelAdapterFactory;

return [
    'router' => [
        'routes' => [
            'timecard' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/timecard',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => TimecardController::class,
                    ],
                ],
                'may_terminate' => TRUE,
                'child_routes' => [
                    'config' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/config[/:action]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => TimecardConfigController::class,
                            ],
                        ],
                    ],
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => TimecardController::class,
                            ],
                        ],
                    ],
                ],
            ],
            'paycode' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/paycode',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => PaycodeController::class,
                    ],
                ],
                'may_terminate' => TRUE,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => PaycodeController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'admin' => [
            'timecard/config' => [],
            'timecard/default' => [],
            'paycode/default' => [],
        ],
    ],
    'controllers' => [
        'factories' => [
            PaycodeController::class => PaycodeControllerFactory::class,
            TimecardConfigController::class => TimecardConfigControllerFactory::class,
            TimecardController::class => TimecardControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            PaycodeForm::class => PaycodeFormFactory::class,
            TimecardForm::class => TimecardFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            'timecard' => [
                'label' => 'Timecard',
                'route' => 'timecard/default',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Pay Codes',
                        'route' => 'paycode/default',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'Add New Pay Code',
                                'route' => 'paycode/default',
                                'action' => 'create',
                            ],
                            [
                                'label' => 'List Pay Codes',
                                'route' => 'paycode/default',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Time Entry',
                        'route' => 'timecard/default',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'Add New Day',
                                'route' => 'timecard/default',
                                'action' => 'create',
                            ],
                            [
                                'label' => 'List Entries',
                                'route' => 'timecard/default',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'label' => 'Settings',
                'pages' => [
                    'timecard' => [
                        'label' => 'Timecard Settings',
                        'route' => 'timecard/config',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'timecard-model-adapter-config' => 'model-adapter-config',
        ],
        'factories' => [
            'timecard-model-adapter' => TimecardModelAdapterFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];