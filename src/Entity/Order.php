<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Table(name: 'orders')]
#[ORM\Index(columns: ['pos_id', 'created_at'], name: 'idx_order_pos_created')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PointOfSale::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'pos_id', nullable: false)]
    private ?PointOfSale $pointOfSale = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPointOfSale(): ?PointOfSale
    {
        return $this->pointOfSale;
    }

    public function setPointOfSale(PointOfSale $pointOfSale): void
    {
        $this->pointOfSale = $pointOfSale;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
