<?php

namespace App\Controller;

use App\Service\SlugifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'listProducts')]
    public function listProducts(): Response
    {
        return $this->render('product/index.html.twig', [
            'listProducts' => 'Liste des produits',
        ]);
    }

    #[Route('/product/{id}', name: 'viewProduct')]
    public function viewProduct(int $id, SlugifyService $slugify): Response
    {
        $title = "T-Shirt d'Été !";
        $slug  = $slugify->slugify($title);

        return $this->render('product/view.html.twig', [
            'id'    => $id,
            'title' => $title,
            'slug'  => $slug,
        ]);
    }
}
