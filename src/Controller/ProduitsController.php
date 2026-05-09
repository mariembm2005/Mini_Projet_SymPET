<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

final class ProduitsController extends AbstractController
{
    #[Route('/prods', name: 'app_prods1')]
    public function affich(ProduitRepository $rep, PaginatorInterface $paginator, HttpFoundationRequest $request): Response
    {
        $produits = $paginator->paginate(
            $rep->findAll(),

            $request->query->getInt('page', 1),
            6
        );
        $search = "";


        return $this->render('produits/prods.html.twig', [
            'produits' => $produits,
            'search' => $search
        ]);
    }
}
