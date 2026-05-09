<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\Produit;
use App\Repository\LignePanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }

    #[Route('/cart_add/{id}', name: 'cart_add')]
    public function ajouter(int $id, EntityManagerInterface $em, LignePanierRepository $repo, ProduitRepository $produitRepo): Response
    {
        $produit = $produitRepo->find($id);
        $ligne = $repo->findOneBy(['produit' => $produit]);
        if ($ligne) {
            $ligne->setQte($ligne->getQte() + 1);
        } else {
            $ligne = new LignePanier();
            $ligne->setProduit($produit);
            $ligne->setQte(1);
            $em->persist($ligne);
            $this->addFlash('success', $produit->getNomProduit() . ' ajouté au panier !');
        }
        $em->flush();
        return $this->redirectToRoute("app_prods1");
    }

    #[Route('/cart_list', name: 'cart_list')]
    public function cart_lis(LignePanierRepository $rep)
    {
        $lignes = $rep->findAll();
        return $this->render('panier/index.html.twig', [
            'paniers' => $lignes
        ]);
    }

    #[Route('/cart_supp/{id}', name: 'cart_supp')]
    public function cart_sup(LignePanierRepository $rep, $id, EntityManagerInterface $em)
    {
        $lignes = $rep->find($id);
        $em->remove($lignes);
        $em->flush();
        return $this->redirectToRoute('cart_list');
    }

    #[Route('/cart_modif/{id}/{action}', name: 'cart_modif')]
    public function cart_mod(string $action, EntityManagerInterface $em, $id, LignePanierRepository $rep): Response
    {
        $ligne = $rep->find($id);
        if ($action === 'plus') {
            $ligne->setQte($ligne->getQte() + 1);
        } elseif ($action === 'moins') {
            if ($ligne->getQte() > 1) {
                $ligne->setQte($ligne->getQte() - 1);
            } else {
                $em->remove($ligne);
            }
        }

        $em->flush();
        $this->addFlash('success', 'Panier mis à jour.');

        return $this->redirectToRoute('cart_list');
    }

    #[Route('/cart_supp_all/', name: 'cart_supp_all')]
    public function cart_sup_all(LignePanierRepository $rep, EntityManagerInterface $em)
    {
        $lignes = $rep->findAll();
        foreach ($lignes as $l) {
            $em->remove($l);
        }

        $em->flush();
        return $this->redirectToRoute('cart_list');
    }
}
