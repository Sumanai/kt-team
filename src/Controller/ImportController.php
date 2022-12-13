<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    #[Route('/import', methods: ['GET'], name: 'app_import_page')]
    public function index(): Response
    {
        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
        ]);
    }

    #[Route('/import', methods: ['POST'], name: 'app_import_file')]
    public function import(Request $request): Response
    {
        $file = $request->files[0] ?? null;

        if (!$file) {
            throw new \Exception('File is needed');
        }

        return $this->json([
            'success' => true,
        ]);
    }
}
