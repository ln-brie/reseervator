<?php 

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;

class MailService 
{
    public function __construct(
        MailerInterface $mailerInterface
    )
    {
        
    }

    /**
     * mail to the applicant that validates the reservation
     * needs to have a link to update / delete 
     */
    public function reservation_approved($reservation) {
        return true;
    }
}