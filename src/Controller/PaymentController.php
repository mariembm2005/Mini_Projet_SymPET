<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Service\MailService;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
            'success_url'          => $this->generateUrl('app_commande_show', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $this->generateUrl('cart_list', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }
}
