<?php

namespace App\Entity;


use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $sku;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $wineHouse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $grapes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $wineSort;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $region;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $specialPrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getWineHouse(): ?string
    {
        return $this->wineHouse;
    }

    public function setWineHouse(string $wineHouse): self
    {
        $this->wineHouse = $wineHouse;

        return $this;
    }

    public function getGrapes(): ?string
    {
        return $this->grapes;
    }

    public function setGrapes(?string $grapes): self
    {
        $this->grapes = $grapes;

        return $this;
    }

    public function getWineSort(): ?string
    {
        return $this->wineSort;
    }

    public function setWineSort(?string $wineSort): self
    {
        $this->wineSort = $wineSort;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getSpecialPrice(): ?float
    {
        return $this->specialPrice;
    }

    public function setSpecialPrice(float $specialPrice): self
    {
        $this->price = $specialPrice;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * This function returns standard null and only returns the percentage if there is a specialPrice
     * @return int|null
     */
    #[Pure] public function calculateDiscountPercentage(): ?int
    {
        $discountPercentage = null;
        //get the special price via the getSpecialPrice function
        $specialPrice = $this->getSpecialPrice();
        //get the standard wine price via the getPrice function
        $winePrice = $this->getPrice();
        //If specialPrice is not null
        if ($specialPrice !== null)
        {
            //return 100 - specialPrice / Price * 100
            // 100 - first because then you see the difference between 100 and the $specialPrice / $winePrice * 100.
            // so if the outcome of $specialPrice / $winePrice * 100 == 87 then the 100 - does 100 - 87
            $discountPercentage = 100 - $specialPrice / $winePrice * 100;
        }

        //If special price is null return null
        //no if statement because the condition is always 'true' because '$specialPrice !== null' is already 'false' at this point
        return $discountPercentage;
    }

    /**
     * @return array|null
     */
    #[ArrayShape(['exactLocation' => "array|string|string[]", 'locationForIMG' => "string"])]
    public function getExactLocationForWine(): ?array
    {
        $exactLocation = $this->location;
        $exactLocation = str_replace(array(',', ' '), '', $exactLocation);
        $locationForIMG = substr($exactLocation, 0, - 1);
        $locations = ['exactLocation' => $exactLocation, 'locationForIMG' => $locationForIMG];

        return $locations;
    }

    public function setExactLocationForWine(?string $exactLocation): self
    {
        $this->exactLocation = $exactLocation;
        return $this;
    }
}
