<?php

namespace App\Controller;

use App\Service\ParseXmlToProduct;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import')]
    public function index(Request $request, ParseXmlToProduct $parser): Response
    {
        // TODO: Вынести форму в отдельный класс
        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->setMethod('POST')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null */
            $file = $form['file']->getData();

            if (!$file) {
                throw new \Exception('File is needed');
            }

            $parser->parse($file);
        }

        return $this->renderForm('import/index.html.twig', [
            'form' => $form,
        ]);
    }
}
