<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\VerificationCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class VerificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private string $mailerFrom = 'siwarsiwar2870@gmail.com'
    ) {}

    public function sendCode(string $email, string $type): string
{
    $code = random_int(100000, 999999);

    $token = new VerificationCode();
    $token->setEmail($email);
    $token->setCode((string)$code);
    $token->setType($type);
    $token->setExpiresAt(new \DateTime('+10 minutes'));

    $this->em->persist($token);
    $this->em->flush();

    $emailMessage = (new Email())
        ->from($this->mailerFrom)
        ->to($email)
        ->subject('Verification Code')
        ->text("Votre code est : $code");

    $this->mailer->send($emailMessage);

    return (string)$code;
}
}