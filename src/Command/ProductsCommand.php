<?php

namespace App\Command;


use App\Entity\Product;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Grandcruwijnen\SDK\Products;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

//Authorization: Bearer <token>


class ProductsCommand extends Command {
    protected static $defaultName = 'app:fill:products';
    protected static $defaultDescription = 'Fills products database';

    private Products $products;
    private EntityManagerInterface $manager;
    private ProductRepository $productRepository;


    public function getAuthToken(): bool|string
    {
        $token = 'tc55zrtlllvm3uffu2ml837mwmtbuea3';

        $httpClient = new Client();

        $response = $httpClient->get(
            'https://httpbin.org/bearer',
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]
        );

        return ($response->getBody()->getContents());
    }

    /**
     * ProductsCommand constructor.
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
        $this->setDescription(self::$defaultDescription);
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $items = $this->products->getProducts()['items'];
        foreach ($items as $magentoProduct)
        {
            /*var_dump($magentoProduct['custom_attributes'][8]['value']);
            die();*/
            if ($magentoProduct['status'] !== 1)
            {
                continue;
            }
            if ( ! isset($magentoProduct['media_gallery_entries'][0]))
            {
                continue;
            }
            $product = $this->productRepository->findOneBy(['sku' => $magentoProduct['sku']]);
            $updatedAt = new DateTime($magentoProduct['updated_at']);
//            $io->comment($magentoProduct['custom_attributes'][8]['value']);
            if ($product === null)
            {
                $product = new Product();
                $product
                    ->setSku($magentoProduct['sku'])
                    ->setUpdatedAt($updatedAt)
                    ->setName($magentoProduct['name'])
                    ->setDescription($magentoProduct['custom_attributes'][1]['value'])
                    ->setPrice($magentoProduct["price"])
                    ->setStock($magentoProduct['extension_attributes']['stock_item']['qty'])
                    ->setImage($magentoProduct['media_gallery_entries'][0]['file'])
                    ->setLand($magentoProduct['custom_attributes'][8]['value']);
            } else if ($updatedAt > $product->getUpdatedAt())
            {
                $product
                    ->setValid(false)
                    ->setCheckedSinceUpdate(false);
            }
            $this->manager->persist($product);
        }

        $this->manager->flush();

        $io->success('Product database updated');

        return Command::SUCCESS;
    }
}