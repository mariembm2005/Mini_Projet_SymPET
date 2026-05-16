<?php

namespace App\Mailer;

use App\Entity\Commande;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints\Email as ConstraintsEmail;

class OrderMailer
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendConfirmation(Commande $commande): void
    {
        $email = (new Email())
            ->from('mariembenmansour005@gmail.com')
            ->to('nimportequoi@nimporte.com')
            ->subject('Confirmation de votre commande #' . $commande->getId())
            ->text("payment de " . $commande->getId() . " est confirmé!!!");

        $this->mailer->send($email);
    }
}
