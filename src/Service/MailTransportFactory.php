<?php
/**
 * Contact
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GPL v3
 */

namespace Contact\Service;

use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions as FileTransportOptions;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions as SmtpTransportOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MailTransportFactory implements FactoryInterface
{
    /**
     * @var array Known transport types
     */
    protected static $transportClassMap = array(
        'file'      => 'Zend\Mail\Transport\File',
        'null'      => 'Zend\Mail\Transport\Null',
        'sendmail'  => 'Zend\Mail\Transport\Sendmail',
        'smtp'      => 'Zend\Mail\Transport\Smtp',
    );

    /**
     * Create and return the mail transport
     *
     * Retrieves the "contact_module" key of the Config service, and uses it
     * to instantiate the mail transport. Uses the Sendmail implementation by
     * default.
     *
     * @param  ServiceLocatorInterface        $serviceLocator
     * @return \Zend\Mail\Transport\TransportInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config          = $serviceLocator->has('Config') ? $serviceLocator->get('Config') : array();
        $transportConfig = isset($config['contact_module']['mail_transport']) ? $config['contact_module']['mail_transport'] : array();

        // If the config is a string then the user is supplying the contact form class
        if (is_string($transportConfig)) {
            $transportConfig = array('type' => $transportConfig);
        }

        $transportClass = 'Zend\Mail\Transport\Sendmail';

        // Obtain the configured transport class, if any
        if (isset($transportConfig['type'])) {
            $normalizedType = strtolower($transportConfig['type']);

            if (isset(static::$transportClassMap[$normalizedType])) {
                $transportConfig['type'] = static::$transportClassMap[$normalizedType];
            }

            if (class_exists($transportConfig['type'])) {
                $transportClass = $transportConfig['type'];
            }
        }

        // Create the transport
        $transport = new $transportClass();

        // Set transport options, if any
        if (isset($transportConfig['options'])) {
            if ($transport instanceof SmtpTransport) {
                $transport->setOptions(new SmtpTransportOptions($transportConfig['options']));
            }

            if ($transport instanceof FileTransport) {
                $transport->setOptions(new FileTransportOptions($transportConfig['options']));
            }
        }

        return $transport;
    }
}