<?php

namespace App\Command;


use App\Entity\Product;
use App\Repository\ProductRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Grandcruwijnen\SDK\Attributes;
use Grandcruwijnen\SDK\Products;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

//[pageSize]=20
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
        $grapeAttributes = $this->attributes->getProductAttributeOptions('druif');
        $wineSortAttributes = $this->attributes->getProductAttributeOptions('wijnsoort');
        $regionAttributes = $this->attributes->getProductAttributeOptions('regio');
        $specialPriceAttributes = $this->attributes->getProductAttributeOptions('special_price');
//        $locationAttributes = $this->attributes->getProductAttributeOptions('lokatie');


        foreach ($items as $magentoProduct) {
            $specialPrice = null;
            foreach ($magentoProduct['custom_attributes'] as $attr) {
                if ($attr['attribute_code'] === 'special_price') {
                    $specialPrice = $attr['value'];
                    break;
                }
            }

            if ($magentoProduct['status'] !== 1) {
                continue;
            }
            if (!isset($magentoProduct['media_gallery_entries'][0])) {
                continue;
            }

            if ($this->findLabelForValue($countryAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'land')) === null) {
                continue;
            }
            if ($this->findLabelForValue($wineHouseAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnhuis')) === null) {
                continue;
            }
            //Todo: investigate multi-select
            if ($this->findLabelForValue($grapeAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'druif')) === null) {
                continue;
            }
            if ($this->findLabelForValue($wineSortAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnsoort')) === null) {
                continue;
            }
            if ($this->findLabelForValue($regionAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'regio')) === null) {
                continue;
            }
            if ($this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'lokatie') === null) {
                continue;
            }
            // if ($this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'special_price') === null)
            // {
            //     continue;
            // }


            $product = $this->productRepository->findOneBy(['sku' => $magentoProduct['sku']]);
            $updatedAt = new DateTime($magentoProduct['updated_at']);
//            $io->comment($locationAttributes);
            if ($product === null) {
                var_dump($magentoProduct['sku']);
                $product = new Product();
                $product->setSpecialPrice($specialPrice);
                $this->updateProduct($product, $magentoProduct, $updatedAt, $countryAttributes, $wineHouseAttributes, $grapeAttributes, $wineSortAttributes, $regionAttributes, $specialPriceAttributes);
            } else {
                $this->updateProduct($product, $magentoProduct, $updatedAt, $countryAttributes, $wineHouseAttributes, $grapeAttributes, $wineSortAttributes, $regionAttributes, $specialPriceAttributes);
                $product->setSpecialPrice($specialPrice);
            }
//            var_dump($product->getCountry());
            $this->manager->persist($product);
        }
        $this->manager->flush();

        $io->success('Product database updated');

        return Command::SUCCESS;
    }

    private function findLabelForValue(array $attributes, ?string $id)
    {
//        var_dump($id);
        if ($id === null) {
            return null;
        }
        foreach ($attributes as $attribute) {
            if ($attribute['value'] === $id) {
                return $attribute['label'];
            }
        }

        return null;
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

    /**
     * @param \App\Entity\Product $product
     * @param mixed $magentoProduct
     * @param \DateTime $updatedAt
     * @param array $countryAttributes
     * @param array $wineHouseAttributes
     * @param array $grapeAttributes
     * @param array $wineSortAttributes
     * @param array $regionAttributes
     * @return void
     */
    protected function updateProduct(Product $product, array $magentoProduct, DateTime $updatedAt, array $countryAttributes, array $wineHouseAttributes, array $grapeAttributes, array $wineSortAttributes, array $regionAttributes, array $specialPriceAttributes): void
    {
        $product
            ->setSku($magentoProduct['sku'])
            ->setUpdatedAt($updatedAt)
            ->setName($magentoProduct['name'])
            ->setDescription($magentoProduct['custom_attributes'][1]['value'])
            ->setPrice($magentoProduct["price"])
            ->setStock($magentoProduct['extension_attributes']['stock_item']['qty'])
            ->setImage($magentoProduct['media_gallery_entries'][0]['file'])
            ->setCountry($this->findLabelForValue($countryAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'land')))
            ->setWineHouse($this->findLabelForValue($wineHouseAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnhuis')))
            ->setGrapes($this->findLabelForValue($grapeAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'druif')))
            ->setWineSort($this->findLabelForValue($wineSortAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'wijnsoort')))
            ->setRegion($this->findLabelForValue($regionAttributes, $this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'regio')))
            ->setLocation($this->findAttributeValueForCode($magentoProduct['custom_attributes'], 'lokatie'));
        // ->setSpecialPrice($this->findAttributeValueForCode($specialPriceAttributes);
    }
}