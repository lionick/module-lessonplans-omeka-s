<?php

return [
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH.'/modules/LessonPlans/view',
        ],
    ],

    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/LessonPlans/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            //'LessonPlans\Controller\Admin\Index' => 'LessonPlans\Controller\Admin\IndexController',
            //'LessonPlans\Controller\Admin\LessonPlanMedia' => 'LessonPlans\Controller\Admin\LessonPlanMediaController',
            //'LessonPlans\Controller\Admin\LessonPlan' => 'LessonPlans\Controller\Admin\LessonPlanController',
        ],
        'factories' => [
            LessonPlans\Controller\Admin\LessonPlan::class => LessonPlans\Service\Controller\Admin\LessonPlanControllerFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'deleteConfirm' => LessonPlans\Service\ViewHelper\DeleteConfirmFactory::class,
        ],
    ],
    'api_adapters' => [
        'invokables' => [
           'lesson-plan-settings' => LessonPlans\Api\Adapter\LessonPlanSettingsAdapter::class,
           'lesson-plans' => LessonPlans\Api\Adapter\LessonPlanAdapter::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
          //  'LessonPlans\Form\ConfigForm' => 'LessonPlans\Service\Form\ConfigFormFactory',
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Lesson Plan', // @translate
                'route' => 'admin/site/slug/lesson-plan/action',
                'action' => 'browse',
                'useRouteMatch' => true,
                'class' => 'o-icon-vocab',
                'pages' => [
                    
                    [
                        'route' => 'admin/site/slug/lesson-plan/action',
                        'visible' => true,
                        'label' => 'Add Lesson Plan',
                        'action' => 'add',
                        'useRouteMatch' => true,
                    ],
                    [
                        'route' => 'admin/site/slug/lesson-plan/action',
                        'visible' => true,
                        'label' => 'Configure Lesson Plan Settings',
                        'action' => 'configure',
                        'useRouteMatch' => true,
                    ],
                    [
                        'route' => 'admin/site/slug/lesson-plan/action',
                        'visible' => false,
                        
                    ],
                    
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'lesson_plan-id' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/lesson-plan/:id[/:action]',
                            'defaults' => [
                                '__NAMESPACE__' => 'LessonPlans\Controller\Admin',
                                'controller' => 'lesson-plan',
                                'action' => 'show',
                            ],
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                        ],
                    ],
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'lesson-plan' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/lesson-plan',
                                            'defaults' => [
                                                '__NAMESPACE__' => 'LessonPlans\Controller\Admin',
                                                'controller' => 'lesson-plan',
                                                'action' => 'browse',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'default' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '[/:action]',
                                                    'constraints' => [
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                    'defaults' => [
                                                        'action' => 'browse',
                                                    ],
                                                ],
                                            ],
                                            'action' => [
                                                'type' => \Laminas\Router\Http\Segment::class,
                                                'options' => [
                                                    'route' => '[/:action]',
                                                    'constraints' => [
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                    ],
                                                ],
                                            ],
                                            'lesson_plan-id' => [
                                                'type' => \Laminas\Router\Http\Segment::class,
                                                'options' => [
                                                    'route' => '/:id[/:action]',
                                                    'defaults' => [
                                                        '__NAMESPACE__' => 'LessonPlans\Controller\Admin',
                                                        'controller' => 'lesson-plan',
                                                        'action' => 'show',
                                                    ],
                                                    'constraints' => [
                                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                        'id' => '\d+',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
     'entity_manager' => [
    //     'is_dev_mode' => false,
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/LessonPlans/src/Entity',
        ],
        'proxy_paths' => [
            OMEKA_PATH. '/data/doctrine-proxies',
        ],
    //     'resource_discriminator_map' => [
    //        // 'LessonPlansEntity\LessonPlan' => LessonPlans\Entity\LessonPlan::class,
    //        // 'LessonPlans\Entity\LessonPlanMedia' => LessonPlans\Entity\LessonPlanMedia::class,
    //     ],
     ],
];
