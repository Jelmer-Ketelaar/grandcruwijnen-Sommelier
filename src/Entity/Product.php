<?php

namespace App\Entity;


use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sku;

    /**
     * @ORM\ManyToOne(targetEntity=ExactGroup::class, inversedBy="products")
     */
    private $exactGroup;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $checkedSinceUpdate;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->valid = false;
        $this->checkedSinceUpdate = false;
    }

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

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getCheckedSinceUpdate(): ?bool
    {
        return $this->checkedSinceUpdate;
    }

    public function setCheckedSinceUpdate(bool $checkedSinceUpdate): self
    {
        $this->checkedSinceUpdate = $checkedSinceUpdate;

        return $this;
    }
}
