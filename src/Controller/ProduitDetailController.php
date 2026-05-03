<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProduitDetailController extends AbstractController
{
    #[Route('/produits/{id}', name: 'app_produit_detail')]
    public function index($id, ProduitRepository $rep): Response
    {
        $prod = $rep->find($id);

        return $this->render('produit_detail/prod_detail.html.twig', [
            'produit' => $prod,
            'id' => $id
        ]);
    }
}
