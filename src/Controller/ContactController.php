<?php
/**
 * Contact
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2015 Juan Pedro Gonzalez Gutierrez
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GPL v3
 */

namespace Contact\Controller;

use Zend\Mail\Message;

class ContactController extends AbstractContactController
{
    /**
     * This is how to set a custom recipient for your controller.
     */
    //protected $mailRecipients = 'your-mail@example.com';

    public function prepareMessage($data)
    {
        $message = new Message();
        $message->addFrom($data['email'])
                ->addReplyTo($data['email'])
                ->setSubject('[Contact Form] ' . $data['subject'])
                ->setBody($data['message']);
        
        return $message;
    }
}