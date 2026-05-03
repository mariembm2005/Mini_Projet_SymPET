<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FiltrerController extends AbstractController
{
    #[Route('/filtrer', name: 'app_filtrer')]
    public function index(ProduitRepository $rep, Request $request): Response
    {
        $search = $request->query->get('search');

        $produits = $rep->findAll();

        if ($search) {

            $produits = array_filter($produits, function ($p) use ($search) {
                return str_contains(strtolower($p->getNomProduit()), strtolower($search))
                    || str_contains(strtolower($p->getCat()->getNomCat()), strtolower($search));
            });
        }
        return $this->render('produits/prods.html.twig', [
            'produits' => $produits,
        ]);
    }
}
