<?php

namespace App\Service;

use App\Entity\Commande;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
    ) {}

    public function sendOrderConfirmation(Commande $commande, MailerInterface $mailer): void
    {
        $html = $this->twig->render('emails/order_confirmation.html.twig', [
            'commande' => $commande,
        ]);

        $email = (new Email())
            ->from('mariembenmansour005@gmail.com')
            ->to('tokaotaku010@gmail.com')
            ->subject('Confirmation de votre commande #' . $commande->getId())
            ->html($html);

        $mailer->send($email);
    }
}
