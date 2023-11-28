<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "orders")]
class Order
{
    const PENDING_PAYMENT = 'pending_payment';
    const PAYMENT_RECEIVED = 'payment_received';
    const FAILED_PAYMENT = 'failed_received';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string")]
    private $customerName;

    #[ORM\Column(type: "integer")]
    private $amount;

    #[ORM\Column(type: "string", nullable: true)]
    private $stripeSessionId;

    #[ORM\Column(type: "string", nullable: true)]
    private $status;

    #[ORM\OneToMany(mappedBy: 'linkedOrder', targetEntity: OrderItem::class, orphanRemoval: true)]
    private Collection $orderItems;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profile $customer = null;

    #[ORM\Column(length: 20)]
    private ?string $stripe_status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    // ... autres propriétés et annotations

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setLinkedOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getLinkedOrder() === $this) {
                $orderItem->setLinkedOrder(null);
            }
        }

        return $this;
    }

    public function getCustomer(): ?Profile
    {
        return $this->customer;
    }

    public function setCustomer(?Profile $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getStripeStatus(): ?string
    {
        return $this->stripe_status;
    }

    public function setStripeStatus(string $stripe_status): static
    {
        $this->stripe_status = $stripe_status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

