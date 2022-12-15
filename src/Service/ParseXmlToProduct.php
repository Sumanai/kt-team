<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Kernel;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use XMLParser;

class ParseXmlToProduct
{
    private int $currentProduct = 0;
    private array $data = [];
    private string $key = '';

    /** @var Category[] */
    private array $allCategories;

    private EntityManagerInterface $manager;

    public function __construct(
        private CategoryRepository $categories,
        private ProductRepository $products,
        private Kernel $kernel,
    ) {

        // $this->allCategories = array_combine(array_column($this->allCategories, 'code'), $this->allCategories);
        foreach ($categories->findAll() as $category) {
            $this->allCategories[$category->getCode()] = $category;
        }

        $this->manager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function parse(UploadedFile $file)
    {
        if ($file->getMimeType() !== 'text/xml') {
            throw new \Exception('Wrong file type');
        }

        $parser = xml_parser_create();

        xml_set_object($parser, $this);
        xml_set_element_handler($parser, 'startXML', 'endXML');
        xml_set_character_data_handler($parser, 'charXML');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

        $stream = fopen($file->getPathname(), 'r');

        while (($data = fread($stream, 16 * 1024))) {
            xml_parse($parser, $data);
        }

        xml_parse($parser, '', true);
        xml_parser_free($parser);
        fclose($stream);

        // Для значений, которые не дошли до 1000
        $this->createProducts();

        // Вручную закатываем солнце
        $this->manager->flush();
    }

    public function startXML(XMLParser $parser, string $name, array $attr): void
    {
        if ($name === 'product' || $name === 'products') {
            // nothing
        } else {
            $this->key = $name;
        }
    }

    public function endXML(XMLParser $parser, string $name): void
    {
        if ($name === 'product') {
            $this->currentProduct++;

            if ($this->currentProduct >= 1000) {
                $this->createProducts();
                $this->currentProduct = 0;
                $this->data = [];
                $this->key = '';
            }
        }
    }

    public function charXML(XMLParser $parser, string $data): void
    {
        $data = trim($data);
        if (!$data) {
            return;
        }

        if (!isset($this->data[$this->currentProduct][$this->key])) {
            $this->data[$this->currentProduct][$this->key] = '';
        }

        $this->data[$this->currentProduct][$this->key] .= $data;
    }

    private function createProducts(): void
    {
        foreach ($this->data as $productData) {
            $productCategory = $this->findCategory($productData['category']);
            $newProduct = new Product();
            $newProduct
                ->setName($productData['name'])
                ->setDescription($productData['description'])
                ->setWeight($this->parseWeight($productData['weight']))
                ->setCategory($productCategory);

            $this->products->save($newProduct);
        }
    }

    private function findCategory(string $categoryName): Category
    {
        $categoryCode = $this->getCategoryCodeByName($categoryName);
        $category = $this->allCategories[$categoryCode] ?? null;

        if ($category) {
            return $category;
        }

        $category = new Category();
        $category
            ->setCode($categoryCode)
            ->setName($categoryName);

        $this->categories->save($category);
        $this->allCategories[$category->getCode()] = $category;

        return $category;
    }

    private function getCategoryCodeByName(string $name): string
    {
        return strtolower(str_replace(' ', '_', $name));
    }

    private function parseWeight(string $strWeight): int
    {
        [$weight, $measure] = explode(' ', $strWeight);

        if ($measure === 'kg') {
            $weight *= 1000;
        }

        return (int) $weight;
    }
}
