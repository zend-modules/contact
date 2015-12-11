<?php
return array(
    'router' => array(
        'routes' => array(
            'contact' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/contact',
                    'defaults' => array(
                        'controller' => 'Contact\Controller\Contact',
                        'action'     => 'index',
                    ),
                ),
                // Optional child route contact/thank-you
                //'may_terminate' => true,
                //'child_routes' => array(
                //    'thank-you' => array(
                //        'type'    => 'Zend\Mvc\Router\Http\Literal',
                //        'options' => array(
                //            'route'    => '/thank-you',
                //            'defaults' => array(
                //                'action' => 'thankYou',
                //            ),
                //        ),
                //    ),
                //),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Contact\Controller\Contact' => 'Contact\Controller\ContactController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'contact' => __DIR__ . '/../view',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Contact\Form'      => 'Contact\Service\ContactFormFactory',
            'Contact\Transport' => 'Contact\Service\MailTransportFactory',
        ),
    ),
    'contact_module' => array(
        //'default_email' => 'your-email@example.com',
    )
);
