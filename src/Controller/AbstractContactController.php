<?php
/**
 * Contact
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GPL v3
 */

namespace Contact\Controller;

use Contact\Exception;
use Contact\Form\ContactForm;
use Zend\Form\FormInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

/**
 * Basic contact action controller
 */
abstract class AbstractContactController extends AbstractActionController
{
    /**
     * The contact form.
     * 
     * @var FormInterface|null
     */
    protected $contactForm;

    /**
     * 
     * @var string|\Zend\Mail\Address\AddressInterface|array|\Zend\Mail\AddressList|Traversable
     */
    protected $mailRecipients;

    /**
     * 
     * @var TransportInterface
     */
    protected $mailTransport;

    /**
     * Prepare the mail message.
     * 
     * @param array $data The form data
     * @return Message
     */
    abstract protected function prepareMessage($data);

    /**
     * Default action if none provided
     *
     * @return array
     */
    public function indexAction()
    {
        $form    = $this->getContactForm();
        $request = $this->getRequest();

        $form->setAttribute('action', $request->getUri()->getPath());       

        if ($request->isPost()) {
            $form->setData( $request->getPost() );
            if (!$form->isValid()) {
                return new ViewModel(array(
                    'display_error' => true,
                    'contact_form'  => $form,
                ));
            }

            try {
                $this->sendMail();
            } catch (\Exception $e) {
                $serviceLocator = $this->getServiceLocator();
                if ($serviceLocator instanceof ServiceLocatorInterface) {
                    if ($serviceLocator->has('ViewManager')) {
                        $viewManager = $serviceLocator->get('ViewManager');
                        if ($viewManager instanceof HttpViewManager) {
                            $exceptionStrategy = $viewManager->getExceptionStrategy();
                            if ($exceptionStrategy->displayExceptions()) {
                                // Rethrow the exception and exit.
                                throw $e;
                                return;
                            }
                        }
                    }                    
                }

                return new ViewModel(array(
                    'display_error' => true,
                    'exception'     => $e,
                    'contact_form'  => $form,
                ));
            }

            // Try to redirect to the contact/thank-you route
            try {
                $response = $this->redirect()->toRoute('contact/thank-you');
                return $response;
            } catch (\Exception $e) {
                // Be silent and continue on.
            }

            return new ViewModel(array(
                'display_success' => true,
                'contact_form'    => $form,
            ));
        }

        return new ViewModel(array(
           'contact_form' => $form,
        ));
    }

    /**
     * Default thank you action if none provided
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function thankYouAction()
    {
        return new ViewModel();
    }

    /**
     * Get the contact form.
     * 
     * @return FormInterface
     */
    public function getContactForm()
    {
        if (!$this->contactForm) {
            // Get the contact form from service manager.
            $serviceLocator = $this->getServiceLocator();

            if ($serviceLocator->has('Contact\Form')) {
                $this->setContactForm( $this->serviceLocator->get('Contact\Form') );
            } else {
                // Defaults to the module's contact form
                $this->contactForm = new ContactForm();
            }
        }

        return $this->contactForm;
    }

    /**
     * Get the mail transport.
     * 
     * @return TransportInterface
     */
    public function getMailTransport()
    {
        if (!$this->mailTransport) {
            // Get the mail transport from the service manager
            $serviceLocator = $this->getServiceLocator();

            if ($serviceLocator->has('Contact\Transport')) {
                $this->setMailTransport( $this->serviceLocator->get('Contact\Transport') );
            } else {
                // Mail transport defaults to Sendmail
                $this->mailTransport = new Sendmail();
            }
        }

        return $this->mailTransport;
    }

    /**
     * Send a mail message.
     * 
     * If no mail message is sent as an argument then it will call the method
     * prepareMessage() in order to forge a new mail message.
     * 
     * @param Message|null $message The mail message (Optional)
     */
    public function sendMail($message = null)
    {
        // Get the mail message
        if (null === $message) {
            // Call the abstract method prepareMessage() to get the mail message for the controller.
            $message = $this->prepareMessage( $this->getContactForm()->getData() );
            if (!$message instanceof Message) {
                throw new Exception\RuntimeException(sprintf(
                    'The method "%s::prepareMessage()" should return an instance of Zend\Mail\Message, %s given.',
                    get_called_class(),
                    (is_object($message) ? get_class($message) : gettype($message))
                ));
            }
        } elseif (!$message instanceof Message) {
            throw new Exception\InvalidArgumentException(sprintf('Message must be an instance of Zend\Mail\Message, %s given.', 
                (is_object($message) ? get_class($message) : gettype($message))
            ));
        }

        // If recipients have not been set, set the controller recipients if any
        if ($message->getTo()->count() <= 0) {
            if (!$this->mailRecipients) {
                $serviceLocator = $this->getServiceLocator();
                if ($serviceLocator instanceof ServiceLocatorInterface) {
                    // Get the default recipients from config
                    $config       = $serviceLocator->has('Config') ? $serviceLocator->get('Config') : array();
                    $recipients   = isset($config['contact_module']['recipients']) ? $config['contact_module']['recipients'] : array();
                    $defaultemail = isset($config['contact_module']['default_email']) ? $config['contact_module']['default_email'] : null;

                    if (empty($defaultemail)) {
                        if (count($recipients) !== 1) {
                            throw new Exception\RuntimeException('No default email address has been configured');
                        }

                        $recipients = array_values($recipients);
                        $this->mailRecipients = $recipients[0];
                    } elseif (!is_string($defaultemail)) {
                        throw new Exception\RuntimeException(sprintf(
                            'The default email address must be a string, %s given.',
                            (is_object($defaultemail) ? get_class($defaultemail) : gettype($defaultemail))
                        ));
                    } elseif (!filter_var($defaultemail, FILTER_VALIDATE_EMAIL) === false) {
                        $this->mailRecipients = $defaultemail;
                    } elseif (isset($recipients[$defaultemail])) {
                        $this->mailRecipients = $recipients[$defaultemail];
                    } else {
                        throw new Exception\RuntimeException('The default recipient must be an email address or a valid recipient name.');
                    }
                }
                
            }
            $message->addTo($this->mailRecipients);
        }

        // Send the mail
        $this->getMailTransport()->send($message);
    }

    /**
     * Set the contact form.
     * 
     * @param FormInterface $form
     * @return AbstractContactController
     */
    public function setContactForm(FormInterface $form)
    {
        $this->contactForm = $form;
        return $this;
    }

    /**
     * Set the mail transport.
     * 
     * @param TransportInterface $transport
     * @return AbstractContactController
     */
    public function setMailTransport(TransportInterface $transport)
    {
        $this->mailTransport = $transport;
        return $this;
    }

}
