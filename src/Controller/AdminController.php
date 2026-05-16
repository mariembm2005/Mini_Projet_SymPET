<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{

    // ========== DASHBOARD ==========

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $produits   = $em->getRepository(Produit::class)->findAll();
        $categories = $em->getRepository(Categorie::class)->findAll();
        $users      = $em->getRepository(User::class)->findAll();
        $commandes  = $em->getRepository(Commande::class)->findAll();

        // Chiffre d'affaires : on additionne les montants de toutes les commandes
        $ca = 0;
        foreach ($commandes as $commande) {
            $ca += $commande->getMontantTotal();
        }

        // Commandes par statut
        $enAttente  = 0;
        $enCours    = 0;
        $completees = 0;
        foreach ($commandes as $commande) {
            if ($commande->getStatut() == 'en_attente') $enAttente++;
            if ($commande->getStatut() == 'en_cours')   $enCours++;
            if ($commande->getStatut() == 'completee')  $completees++;
        }

        // Nombre de commandes par mois (pour le graphique)
        $commandesParMois = [];
        foreach ($commandes as $commande) {
            $mois = $commande->getDateCommande()->format('Y-m');
            if (!isset($commandesParMois[$mois])) {
                $commandesParMois[$mois] = 0;
            }
            $commandesParMois[$mois]++;
        }
        krsort($commandesParMois);
        $commandesParMois = array_slice($commandesParMois, 0, 6, true);

        // Produits les plus vendus (via lignes de commande)
        $ventesParProduit = [];
        foreach ($commandes as $commande) {
            foreach ($commande->getLigneCommandes() as $ligne) {
                $nom = $ligne->getProduit() ? $ligne->getProduit()->getNomProduit() : 'Inconnu';
                if (!isset($ventesParProduit[$nom])) {
                    $ventesParProduit[$nom] = 0;
                }
                $ventesParProduit[$nom] += $ligne->getQuantite();
            }
        }
        arsort($ventesParProduit);
        $ventesParProduit = array_slice($ventesParProduit, 0, 5, true);

        return $this->render('admin/dashboard.html.twig', [
            'totalProduits'    => count($produits),
            'totalCategories'  => count($categories),
            'totalUsers'       => count($users),
            'totalCommandes'   => count($commandes),
            'ca'               => $ca,
            'enAttente'        => $enAttente,
            'enCours'          => $enCours,
            'completees'       => $completees,
            'commandesParMois' => $commandesParMois,
            'ventesParProduit' => $ventesParProduit,
        ]);
    }

    // ========== PRODUITS ==========

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function produits(EntityManagerInterface $em): Response
    {
        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('admin/produits/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/admin/produits/nouveau', name: 'app_admin_produit_nouveau')]
    public function produitNouveau(Request $request, EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();

        if ($request->isMethod('POST')) {

            $produit = new Produit();
            $produit->setNomProduit($request->request->get('nomProduit'));
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float) $request->request->get('prix'));
            $produit->setStock((int) $request->request->get('stock'));
            $produit->setDateAjout(new \DateTime());
            $produit->setImage($request->request->get('image'));

            $catId = $request->request->get('cat');
            if ($catId) {
                $cat = $em->getRepository(Categorie::class)->find($catId);
                $produit->setCat($cat);
            }

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_admin_produits');
        }

        return $this->render('admin/produits/form.html.twig', [
            'produit'    => null,
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/produits/{id}/modifier', name: 'app_admin_produit_modifier')]
    public function produitModifier(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $produit    = $em->getRepository(Produit::class)->find($id);
        $categories = $em->getRepository(Categorie::class)->findAll();

        if ($request->isMethod('POST')) {

            $produit->setNomProduit($request->request->get('nomProduit'));
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float) $request->request->get('prix'));
            $produit->setStock((int) $request->request->get('stock'));
            $produit->setImage($request->request->get('image'));

            $catId = $request->request->get('cat');
            if ($catId) {
                $cat = $em->getRepository(Categorie::class)->find($catId);
                $produit->setCat($cat);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('app_admin_produits');
        }

        return $this->render('admin/produits/form.html.twig', [
            'produit'    => $produit,
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/produits/{id}/supprimer', name: 'app_admin_produit_supprimer', methods: ['POST'])]
    public function produitSupprimer(int $id, EntityManagerInterface $em): Response
    {
        $produit = $em->getRepository(Produit::class)->find($id);

        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé.');
        return $this->redirectToRoute('app_admin_produits');
    }

    // ========== CATEGORIES ==========

    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function categories(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/categories/nouvelle', name: 'app_admin_categorie_nouvelle')]
    public function categorieNouvelle(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $categorie = new Categorie();
            $categorie->setNomCat($request->request->get('nomCat'));
            $categorie->setDescription($request->request->get('description'));

            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée.');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/form.html.twig', [
            'categorie' => null,
        ]);
    }

    #[Route('/admin/categories/{id}/modifier', name: 'app_admin_categorie_modifier')]
    public function categorieModifier(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $categorie = $em->getRepository(Categorie::class)->find($id);

        if ($request->isMethod('POST')) {

            $categorie->setNomCat($request->request->get('nomCat'));
            $categorie->setDescription($request->request->get('description'));

            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée.');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/form.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/admin/categories/{id}/supprimer', name: 'app_admin_categorie_supprimer', methods: ['POST'])]
    public function categorieSupprimer(int $id, EntityManagerInterface $em): Response
    {
        $categorie = $em->getRepository(Categorie::class)->find($id);

        $em->remove($categorie);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée.');
        return $this->redirectToRoute('app_admin_categories');
    }

    // ========== COMMANDES ==========

    #[Route('/admin/commandes', name: 'app_admin_commandes')]
    public function commandes(Request $request, EntityManagerInterface $em): Response
    {
        $statut = $request->query->get('statut', '');

        if ($statut) {
            $commandes = $em->getRepository(Commande::class)->findBy(['statut' => $statut]);
        } else {
            $commandes = $em->getRepository(Commande::class)->findAll();
        }

        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $commandes,
            'statut'    => $statut,
        ]);
    }

    #[Route('/admin/commandes/{id}', name: 'app_admin_commande_detail')]
    public function commandeDetail(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        return $this->render('admin/commandes/detail.html.twig', [
            'commande' => $commande,
        ]);
    }

    // ========== UTILISATEURS ==========

    #[Route('/admin/utilisateurs', name: 'app_admin_utilisateurs')]
    public function utilisateurs(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/utilisateurs/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/utilisateurs/{id}/modifier', name: 'app_admin_utilisateur_modifier')]
    public function utilisateurModifier(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if ($request->isMethod('POST')) {

            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setAdresse($request->request->get('adresse'));
            $user->setTelephone((int) $request->request->get('telephone'));
            $user->setRole($request->request->get('role'));

            $newPassword = $request->request->get('password');
            if ($newPassword) {
                $user->setMdp($hasher->hashPassword($user, $newPassword));
            }

            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('app_admin_utilisateurs');
        }

        return $this->render('admin/utilisateurs/form.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/utilisateurs/{id}/supprimer', name: 'app_admin_utilisateur_supprimer', methods: ['POST'])]
    public function utilisateurSupprimer(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('app_admin_utilisateurs');
    }
}
