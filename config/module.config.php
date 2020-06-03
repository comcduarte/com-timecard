<?php
namespace Timecard;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Timecard\Controller\DepartmentController;
use Timecard\Controller\PaycodeController;
use Timecard\Controller\TimecardConfigController;
use Timecard\Controller\TimecardController;
use Timecard\Controller\TimecardSignatureController;
use Timecard\Controller\Factory\DepartmentControllerFactory;
use Timecard\Controller\Factory\PaycodeControllerFactory;
use Timecard\Controller\Factory\TimecardConfigControllerFactory;
use Timecard\Controller\Factory\TimecardControllerFactory;
use Timecard\Controller\Factory\TimecardSignatureControllerFactory;
use Timecard\Form\PaycodeForm;
use Timecard\Form\TimecardForm;
use Timecard\Form\TimecardSignatureForm;
use Timecard\Form\Factory\PaycodeFormFactory;
use Timecard\Form\Factory\TimecardFormFactory;
use Timecard\Form\Factory\TimecardSignatureFormFactory;
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
                    'signatures' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/sign[/:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => TimecardSignatureController::class,
                            ],
                        ],
                    ],
                    'timesheet' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/timesheet[/:uuid[/:week]]',
                            'defaults' => [
                                'action' => 'timesheet',
                                'controller' => TimecardController::class,
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
            'dept' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/dept',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => DepartmentController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => DepartmentController::class,
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
            DepartmentController::class => DepartmentControllerFactory::class,
            TimecardSignatureController::class => TimecardSignatureControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            PaycodeForm::class => PaycodeFormFactory::class,
            TimecardForm::class => TimecardFormFactory::class,
            TimecardSignatureForm::class => TimecardSignatureFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            'timecard' => [
                'label' => 'Timecard',
                'route' => 'timecard/default',
                'class' => 'dropdown',
                'resource' => 'timecard/default',
                'privilege' => 'menu',
                'pages' => [
                    [
                        'label' => 'Time Sheet',
                        'route' => 'timecard/timesheet',
                        'resource' => 'timecard/timesheet',
                        'privilege' => 'timesheet',
                        'action' => 'timesheet',
                    ],
                    [
                        'label' => 'Pay Codes',
                        'route' => 'paycode/default',
                        'class' => 'dropdown-submenu',
                        'resource' => 'paycode/default',
                        'privilege' => 'index',
                        'pages' => [
                            [
                                'label' => 'Add New Pay Code',
                                'route' => 'paycode/default',
                                'action' => 'create',
                                'resource' => 'paycode/default',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Pay Codes',
                                'route' => 'paycode/default',
                                'action' => 'index',
                                'resource' => 'paycode/default',
                                'privilege' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Signatures',
                        'route' => 'timecard/signatures',
                        'resource' => 'timecard/signatures',
                        'privilege' => 'menu',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'Add New Signature',
                                'route' => 'timecard/signatures',
                                'action' => 'create',
                                'resource' => 'timecard/signatures',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Signatures',
                                'route' => 'timecard/signatures',
                                'action' => 'index',
                                'resource' => 'timecard/signatures',
                                'privilege' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Time Entry',
                        'route' => 'timecard/default',
                        'class' => 'dropdown-submenu',
                        'resource' => 'timecard/default',
                        'privilege' => 'index',
                        'pages' => [
                            [
                                'label' => 'Add New Day',
                                'route' => 'timecard/default',
                                'action' => 'create',
                                'resource' => 'timecard/default',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Entries',
                                'route' => 'timecard/default',
                                'action' => 'index',
                                'resource' => 'timecard/default',
                                'privilege' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'label' => 'Settings',
                'pages' => [
                    [
                        'label' => 'Timecard Settings',
                        'route' => 'timecard/config',
                        'action' => 'index',
                        'resource' => 'timecard/config',
                        'privilege' => 'index',
                    ],
                ],
            ],
            'department' => [
                'label' => 'Preparer',
                'route' => 'dept/default',
                'class' => 'dropdown',
                'resource' => 'dept/default',
                'privilege' => 'menu',
                'pages' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'dept/default',
                        'resource' => 'dept/default',
                        'privilege' => 'index',
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