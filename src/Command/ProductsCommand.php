<?php

namespace App\Command;

use App\Entity\Product;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Grandcruwijnen\SDK\Products;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProductsCommand extends Command
{
    protected static $defaultName = 'app:fill:products';
    protected static $defaultDescription = 'Fills products database';

    private Products $products;
    private EntityManagerInterface $manager;
    private ProductRepository $productRepository;

    /**
     * FillProductsCommand constructor.
     * @param Products $products
     * @param EntityManagerInterface $manager
     * @param ProductRepository $productRepository
     */
    public function __construct(Products $products, EntityManagerInterface $manager, ProductRepository $productRepository)
    {
        parent::__construct();

        $this->products = $products;
        $this->manager = $manager;
        $this->productRepository = $productRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->products->getProducts()['items'] as $magentoProduct) {
            $product = $this->productRepository->findOneBy(['sku' => $magentoProduct['sku']]);
            $updatedAt = new DateTime($magentoProduct['updated_at']);
            if ($product === null) {
                $product = new Product();
                $product
                    ->setSku($magentoProduct['sku'])
                    ->setUpdatedAt($updatedAt);
            } else {
                if ($updatedAt > $product->getUpdatedAt()) {
                    $product
                        ->setValid(false)
                        ->setCheckedSinceUpdate(false);
                }
            }

            $this->manager->persist($product);
        }

        $this->manager->flush();

        $io->success('Product database updated');

        return Command::SUCCESS;
    }
}