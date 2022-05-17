<?php
namespace Timecard;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Timecard\Controller\CronController;
use Timecard\Controller\DashboardController;
use Timecard\Controller\PaycodeController;
use Timecard\Controller\ShiftCodeController;
use Timecard\Controller\TimecardConfigController;
use Timecard\Controller\TimecardController;
use Timecard\Controller\TimecardLineController;
use Timecard\Controller\TimecardSignatureController;
use Timecard\Controller\TimecardStageController;
use Timecard\Controller\Factory\CronControllerFactory;
use Timecard\Controller\Factory\DashboardControllerFactory;
use Timecard\Controller\Factory\PaycodeControllerFactory;
use Timecard\Controller\Factory\ShiftCodeControllerFactory;
use Timecard\Controller\Factory\TimecardConfigControllerFactory;
use Timecard\Controller\Factory\TimecardControllerFactory;
use Timecard\Controller\Factory\TimecardLineControllerFactory;
use Timecard\Controller\Factory\TimecardSignatureControllerFactory;
use Timecard\Controller\Factory\TimecardStageControllerFactory;
use Timecard\Form\PaycodeForm;
use Timecard\Form\TimecardAddForm;
use Timecard\Form\TimecardForm;
use Timecard\Form\TimecardLineForm;
use Timecard\Form\TimecardSignatureForm;
use Timecard\Form\TimecardStageForm;
use Timecard\Form\Factory\PaycodeFormFactory;
use Timecard\Form\Factory\TimecardAddFormFactory;
use Timecard\Form\Factory\TimecardFormFactory;
use Timecard\Form\Factory\TimecardLineFormFactory;
use Timecard\Form\Factory\TimecardSignatureFormFactory;
use Timecard\Form\Factory\TimecardStageFormFactory;
use Timecard\Listener\NotificationListener;
use Timecard\Listener\Factory\NotificationListenerFactory;
use Timecard\Model\TimecardModel;
use Timecard\Navigation\Factory\SignatureNavigationFactory;
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
                    'cron' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/cron[/:action]',
                            'defaults' => [
                                'action' => 'default',
                                'controller' => CronController::class,
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
                    'secure_signatures' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/secure_sign[/:action[/:uuid[/:week]]]',
                            'defaults' => [
                                'controller' => TimecardSignatureController::class,
                            ],
                            'constraints' => [
                                'uuid' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
                                'week' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                            ],
                        ],
                    ],
                    'stages' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/stage[/:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => TimecardStageController::class,
                            ],
                        ],
                    ],
                    'lines' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/line[/:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => TimecardLineController::class,
                            ],
                        ],
                    ],
                    'timesheet' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/timesheet[/:uuid][/:week]',
                            'defaults' => [
                                'action' => 'timesheet',
                                'controller' => TimecardController::class,
                            ],
                            'constraints' => [
                                'uuid' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
                                'week' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
                            ],
                        ],
                    ],
                    'timecards' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/card[/:uuid[/:week]]',
                            'defaults' => [
                                'action' => 'index',
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
            'dashboard' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/dashboard',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => DashboardController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:week]]',
                            'defaults' => [
                                'controller' => DashboardController::class,
                            ],
                        ],
                    ],
                    'dept' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:uuid[/:week]]',
                            'defaults' => [
                                'action' => 'dept',
                                'controller' => DashboardController::class,
                            ],
                            'constraints' => [
                                'uuid' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
                            ],
                        ],
                    ],
                    'department' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/department/[:uuid[/:week]]',
                            'defaults' => [
                                'action' => 'department',
                                'controller' => DashboardController::class,
                            ],
                            'constraints' => [
                                'uuid' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
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
            'shiftcode' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/shiftcode',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => ShiftCodeController::class,
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
                                'controller' => ShiftCodeController::class,
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
            'shiftcode/default' => [],
        ],
    ],
    'controllers' => [
        'factories' => [
            CronController::class => CronControllerFactory::class,
            DashboardController::class => DashboardControllerFactory::class,
            PaycodeController::class => PaycodeControllerFactory::class,
            ShiftCodeController::class => ShiftCodeControllerFactory::class,
            TimecardConfigController::class => TimecardConfigControllerFactory::class,
            TimecardController::class => TimecardControllerFactory::class,
            TimecardLineController::class => TimecardLineControllerFactory::class,
            TimecardSignatureController::class => TimecardSignatureControllerFactory::class,
            TimecardStageController::class => TimecardStageControllerFactory::class,
        ],
    ],
    'event_manager' => [
        'lazy_listeners' => [
            [
                'listener' => NotificationListener::class,
                'method' => 'notify',
                'event' => TimecardModel::EVENT_SUBMITTED,
                'priority' => -100,
            ]
        ],
    ],
    'form_elements' => [
        'factories' => [
            PaycodeForm::class => PaycodeFormFactory::class,
            TimecardAddForm::class => TimecardAddFormFactory::class,
            TimecardForm::class => TimecardFormFactory::class,
            TimecardLineForm::class => TimecardLineFormFactory::class,
            TimecardSignatureForm::class => TimecardSignatureFormFactory::class,
            TimecardStageForm::class => TimecardStageFormFactory::class,
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
                        'privilege' => 'menu',
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
                        'label' => 'Shift Codes',
                        'route' => 'shiftcode/default',
                        'class' => 'dropdown-submenu',
                        'resource' => 'shiftcode/default',
                        'privilege' => 'menu',
                        'pages' => [
                            [
                                'label' => 'Add New Shift Code',
                                'route' => 'shiftcode/default',
                                'action' => 'create',
                                'resource' => 'shiftcode/default',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Shift Codes',
                                'route' => 'shiftcode/default',
                                'action' => 'index',
                                'resource' => 'shiftcode/default',
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
                        ],
                    ],
                    [
                        'label' => 'Stages',
                        'route' => 'timecard/stages',
                        'resource' => 'timecard/stages',
                        'privilege' => 'menu',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'Add New Stage',
                                'route' => 'timecard/stages',
                                'action' => 'create',
                                'resource' => 'timecard/stages',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Stages',
                                'route' => 'timecard/stages',
                                'action' => 'index',
                                'resource' => 'timecard/stages',
                                'privilege' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Time Card Lines',
                        'route' => 'timecard/lines',
                        'class' => 'dropdown-submenu',
                        'resource' => 'timecard/lines',
                        'privilege' => 'menu',
                        'pages' => [
                            [
                                'label' => 'Add New Line',
                                'route' => 'timecard/lines',
                                'action' => 'create',
                                'resource' => 'timecard/lines',
                                'privilege' => 'admin',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Time Cards',
                        'route' => 'timecard/timecards',
                        'class' => 'dropdown-submenu',
                        'resource' => 'timecard/timecards',
                        'privilege' => 'menu',
                        'pages' => [
                            [
                                'label' => 'Add New Timecard',
                                'route' => 'timecard/timecards',
                                'action' => 'create',
                                'resource' => 'timecard/timecards',
                                'privilege' => 'create',
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
            'dashboards' => [
                'label' => 'Dashboard',
                'route' => 'dashboard/default',
                'class' => 'dropdown',
                'resource' => 'dashboard/dept',
                'privilege' => 'menu',
                'pages' => [
                    'department' => [
                        'label' => 'Department Dashboard',
                        'route' => 'dashboard/department',
                        'resource' => 'dashboard/department',
                        'privilege' => 'department',
                        'action' => 'department',
                    ],
                    'payroll' => [
                        'label' => 'Payroll Dashboard',
                        'route' => 'dashboard/default',
                        'resource' => 'dashboard/default',
                        'privilege' => 'payroll',
                        'action' => 'payroll',
                    ],
                ],
            ],
        ],
        'signatures' => [
            'signatures' => [
                'label' => 'Sign Here',
                'route' => 'timecard/secure_signatures',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Submit Weekly Timesheet',
                        'route' => 'timecard/secure_signatures',
                        'action' => 'submit',
                        'resource' => 'timecard/secure_signatures',
                        'privilege' => 'submit',
                    ],
                    [
                        'label' => 'Mark Timesheets Prepared',
                        'route' => 'timecard/secure_signatures',
                        'action' => 'prepare',
                        'resource' => 'timecard/secure_signatures',
                        'privilege' => 'prepare',
                    ],
                    [
                        'label' => 'Approve Department Timesheets',
                        'route' => 'timecard/secure_signatures',
                        'action' => 'approve',
                        'resource' => 'timecard/secure_signatures',
                        'privilege' => 'approve',
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
            'signatures' => SignatureNavigationFactory::class,
            NotificationListener::class => NotificationListenerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'timecard/config' => __DIR__ . '/../view/timecard/config/index.phtml',
            'timecard/cron' => __DIR__ . '/../view/timecard/config/cron.phtml',
            'dept/dashboard' => __DIR__ . '/../view/timecard/partials/dept-dashboard.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];