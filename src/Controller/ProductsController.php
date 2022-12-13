<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $products): Response
    {
        $page = $request->query->getInt('page', 1);
        $productsPaginator = $products->findWithPages($page);

        return $this->render('products/index.html.twig', [
            'products' => $productsPaginator->getIterator(),
            'count' => $productsPaginator->count(),
        ]);
    }
}
