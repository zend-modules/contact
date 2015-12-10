<?php
/**
 * Contact
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GPL v3
 */

namespace Contact\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ContactFormFactory implements FactoryInterface
{
    /**
     * Create and return the contact form
     *
     * Retrieves the "contact_module" key of the Config service, and uses it
     * to instantiate the contact form. Uses the ContactForm implementation by
     * default.
     *
     * @param  ServiceLocatorInterface        $serviceLocator
     * @return \Zend\Form\FormInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->has('Config') ? $serviceLocator->get('Config') : array();
        $formConfig = isset($config['contact_module']['form']) ? $config['contact_module']['form'] : array();

        // If the config is a string then the user is supplying the contact form class
        if (is_string($formConfig)) {
            $formConfig = array('type' => $formConfig);
        }

        $formClass = 'Contact\Form\ContactForm';
        $formName  = 'contactForm';

        // Obtain the configured contact form class, if any
        if (isset($formConfig['type']) && class_exists($formConfig['type'])) {
            $formClass = $formConfig['type'];
        }

        // Obtain the configured contact form name, if any
        if (isset($formConfig['name']) && is_string($formConfig['name']) && !empty($formConfig['name'])) {
            $formName = $formConfig['name'];
        }

        return new $formClass($formName);
    }
}