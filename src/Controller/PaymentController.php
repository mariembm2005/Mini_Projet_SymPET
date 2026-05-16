<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Mailer\OrderMailer;
use App\Repository\CommandeRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Forwarding\Request;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaymentController extends AbstractController
{

    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }

    #[Route('/paiement/stripe/{id}', name: 'app_paiement_stripe')]
    public function stripe(int $id, CommandeRepository $cr, string $stripeSK): Response
    {
        $commande = $cr->find($id);

        Stripe::setApiKey($stripeSK);

        $lineItems = [];
        foreach ($commande->getLigneCommandes() as $ligne) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name' => $ligne->getProduit()->getNomProduit(),
                    ],
                    'unit_amount'  => (int) ($ligne->getPrixUnitaire() * 100),
                ],
                'quantity' => $ligne->getQuantite(),
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url' => $this->generateUrl('app_paiement_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('cart_list', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }
    #[Route('/paiement/success/{id}', name: 'app_paiement_success')]
    public function success(
        int $id,
        CommandeRepository $cr,
        EntityManagerInterface $em,
        OrderMailer $mailer,
    ): Response {
        $commande = $cr->find($id);

        $commande->setStatut('Payée');
        $em->flush();

        $mailer->sendConfirmation($commande);

        return $this->render('payment/success.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/test-email', name: 'app_test_email')]
    public function testEmail(OrderMailer $mailer, CommandeRepository $cr): Response
    {
        $commande = $cr->find(13);
        $mailer->sendConfirmation($commande);
        dd('email envoyé !' . $commande->getId());
    }
}
