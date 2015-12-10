<?php
/**
 * Contact
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GPL v3
 */

namespace Contact\Form;

use Zend\Form\Form;

class ContactForm extends Form
{
    /**
     * @var CaptchaAdapter
     */
    protected $captcha;
    
    public function __construct($name = null, CaptchaAdapter $captcha = null)
    {
        parent::__construct($name);
    
        $this->captcha = $captcha;
    
        $this->init();
    }
    
    public function init()
    {
        $name = $this->getName();
        if (null === $name) {
            $this->setName('contactForm');
        }
    
        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Your name',
            ),
            'type'  => 'Text',
            'attributes' => array(
                'class'             => 'form-control',
                'data-msg-required' => 'Please enter your name.',
            ),
        ));
    
        $this->add(array(
            'type' => 'Zend\Form\Element\Email',
            'name' => 'email',
            'options' => array(
                'label' => 'Your email address',
            ),
            'attributes' => array(
                'class'             => 'form-control',
                'data-msg-required' => 'Please enter your email address.',
                'data-msg-email'    => 'Please enter a valid email.'
            ),
        ));
    
        $this->add(array(
            'name' => 'subject',
            'options' => array(
                'label' => 'Subject',
            ),
            'type'  => 'Text',
            'attributes' => array(
                'class'      => 'form-control',
                'data-msg-required' => 'Please enter the subject.'
                //'max-length' => '100'
            ),
        ));
    
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'message',
            'options' => array(
                'label' => 'Message',
            ),
            'attributes' => array(
                'class'             => 'form-control',
                'data-msg-required' => 'Please enter your message.',
                'rows'              => '10'
            ),
        ));
    
        if (null !== $this->captcha) {
            $this->add(array(
                'type' => 'Zend\Form\Element\Captcha',
                'name' => 'captcha',
                'options' => array(
                    'label' => 'Please verify you are human.',
                    'captcha' => $this->captcha,
                ),
            ));
        }
    
        $this->add(new \Zend\Form\Element\Csrf('security'));
    
        $this->add(array(
            'name' => 'send',
            'type'  => 'Submit',
            'attributes' => array(
                'value' => 'Send Message',
                'class' => 'btn btn-primary btn-lg mb-xlg',
                'data-loading-text' => 'Loading...',
            ),
        ));
    
        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}