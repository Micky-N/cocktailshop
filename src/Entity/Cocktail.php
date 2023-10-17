<?php

namespace App\Entity;

use App\Repository\CocktailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CocktailRepository::class)]
#[ORM\Table(name: 'cocktails')]
class Cocktail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $vip = null;

    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isVip(): ?bool
    {
        return $this->vip;
    }

    public function setVip(bool $vip): static
    {
        $this->vip = $vip;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url ?? 'https://images.pexels.com/photos/2795026/pexels-photo-2795026.jpeg?cs=srgb&dl=pexels-dima-valkov-2795026.jpg&fm=jpg';
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
