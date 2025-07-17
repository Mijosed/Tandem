<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['subscription:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['subscription:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['subscription:read']],
            denormalizationContext: ['groups' => ['subscription:write']]
        ),
        new Put(
            normalizationContext: ['groups' => ['subscription:read']],
            denormalizationContext: ['groups' => ['subscription:write']]
        ),
        new Delete()
    ]
)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscription:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'subscription')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['subscription:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Groups(['subscription:read', 'subscription:write'])]
    #[Assert\Choice(choices: ['free', 'premium'])]
    private ?string $plan = 'free';

    #[ORM\Column(length: 50)]
    #[Groups(['subscription:read', 'subscription:write'])]
    #[Assert\Choice(choices: ['active', 'inactive', 'cancelled', 'expired'])]
    private ?string $status = 'active';

    #[ORM\Column(length: 255, nullable: true, name: 'stripe_subscription_id')]
    #[Groups(['subscription:read', 'subscription:write'])]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'current_period_start')]
    #[Groups(['subscription:read', 'subscription:write'])]
    private ?\DateTimeInterface $currentPeriodStart = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'current_period_end')]
    #[Groups(['subscription:read', 'subscription:write'])]
    private ?\DateTimeInterface $currentPeriodEnd = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    #[Groups(['subscription:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Groups(['subscription:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->currentPeriodStart = new \DateTime();
        $this->currentPeriodEnd = new \DateTime('+1 month');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): static
    {
        $this->plan = $plan;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(?string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        return $this;
    }

    public function getCurrentPeriodStart(): ?\DateTimeInterface
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(?\DateTimeInterface $currentPeriodStart): static
    {
        $this->currentPeriodStart = $currentPeriodStart;
        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeInterface
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?\DateTimeInterface $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
