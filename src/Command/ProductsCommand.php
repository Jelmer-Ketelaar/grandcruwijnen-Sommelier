<?php

namespace App\Command;


use App\Entity\Product;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Grandcruwijnen\SDK\Attributes;
use Grandcruwijnen\SDK\Products;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
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
    private Attributes $attributes;

    /**
     * ProductsCommand constructor.
     * @param Products $products
     * @param EntityManagerInterface $manager
     * @param ProductRepository $productRepository
     */

    public function __construct(Products $products, EntityManagerInterface $manager, ProductRepository $productRepository, Attributes $attributes)
    {
        parent::__construct();

        $this->products = $products;
        $this->manager = $manager;
        $this->productRepository = $productRepository;
        $this->attributes = $attributes;
    }

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
        $countryAttributes = $this->attributes->getProductAttributeOptions('land');
        $wineHouseAttributes = $this->attributes->getProductAttributeOptions('wijnhuis');

        foreach ($items as $magentoProduct) {
            if ($magentoProduct['status'] !== 1) {
                continue;
            }
            if (!isset($magentoProduct['media_gallery_entries'][0])) {
                continue;
            }
            if ($this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'land') === null) {
                continue;
            }
            if ($this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnhuis') === null) {
                continue;
            }
            $product = $this->productRepository->findOneBy(['sku' => $magentoProduct['sku']]);
            $updatedAt = new DateTime($magentoProduct['updated_at']);
//            $io->comment($magentoProduct['custom_attributes'][8]['value']);
            if ($product === null) {
                var_dump($magentoProduct['sku']);
                $product = new Product();
                $product
                    ->setSku($magentoProduct['sku'])
                    ->setUpdatedAt($updatedAt)
                    ->setName($magentoProduct['name'])
                    ->setDescription($magentoProduct['custom_attributes'][1]['value'])
                    ->setPrice($magentoProduct["price"])
                    ->setStock($magentoProduct['extension_attributes']['stock_item']['qty'])
                    ->setImage($magentoProduct['media_gallery_entries'][0]['file'])
                    ->setLand($this->findCountryForId($countryAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'land')))
                    ->setWineHouse($this->findCountryForId($wineHouseAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnhuis')));
            } else if ($updatedAt > $product->getUpdatedAt()) {
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

    private function findAttributeValueForCode(array $attributes, string $code)
    {
        foreach ($attributes as $attribute) {
            if ($attribute['attribute_code'] === $code) {
                return $attribute['value'];
            }
        }
        return null;
    }

    private function findCountryForId(array $countryAttributes, string $id)
    {
        var_dump($id);
        foreach ($countryAttributes as $attribute) {
            if ($attribute['value'] === $id) {
                return $attribute['label'];
            }
        }
        return null;
    }
}