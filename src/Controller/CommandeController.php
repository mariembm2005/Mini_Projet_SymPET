<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\LignePanierRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'app_commande')]
    public function add_com(EntityManagerInterface $em, LignePanierRepository $lpan): Response
    {
        $panier = $lpan->findAll();

        if (!$panier || $panier == []) {
            $this->addFlash('warning', "panier vide!");
            return $this->redirectToRoute('cart_list');
        }

        $commande = new Commande();
        $commande
            ->setDateCommande(new \DateTime())
            ->setStatut('En attente')
            ->setMontantTotal(0);

        $em->persist($commande);

        $total = 0;

        foreach ($panier as $ligne) {
            $sousTotal = $ligne->getProduit()->getPrix() * $ligne->getQte();
            $total += $sousTotal;


            $ligneCommande = new LigneCommande();
            $ligneCommande
                ->setCommande($commande)
                ->setProduit($ligne->getProduit())
                ->setQuantite($ligne->getQte())
                ->setPrixUnitaire($ligne->getProduit()->getPrix());
            $em->persist($ligneCommande);
            $em->remove($ligne);
        }



        $commande->setMontantTotal($total);

        $em->flush();


        return $this->redirectToRoute('app_paiement_stripe', ['id' => $commande->getId()]);
    }

    #[Route('/commande/lis/', name: 'app_commande_show')]
    public function show(CommandeRepository $cm): Response
    {
        $commande = $cm->findAll();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commande,
        ]);
    }


    // Détail — une seule commande
    #[Route('/commande/detail/{id}', name: 'app_commande_detail')]
    public function detail(int $id, CommandeRepository $cm): Response
    {
        $commande = $cm->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable');
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }
}
