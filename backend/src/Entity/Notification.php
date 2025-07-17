<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['notification:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['notification:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['notification:read']],
            denormalizationContext: ['groups' => ['notification:write']]
        ),
        new Put(
            normalizationContext: ['groups' => ['notification:read']],
            denormalizationContext: ['groups' => ['notification:write']]
        ),
        new Delete()
    ]
)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['notification:read', 'notification:write'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['notification:read', 'notification:write'])]
    #[Assert\NotBlank]
    private ?string $message = null;

    #[ORM\Column(length: 50)]
    #[Groups(['notification:read', 'notification:write'])]
    #[Assert\Choice(choices: ['reminder', 'interview', 'info', 'warning', 'success'])]
    private ?string $type = 'info';

    #[ORM\Column(type: 'boolean', name: 'is_read')]
    #[Groups(['notification:read', 'notification:write'])]
    private bool $isRead = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'scheduled_at')]
    #[Groups(['notification:read', 'notification:write'])]
    private ?\DateTimeInterface $scheduledFor = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getScheduledFor(): ?\DateTimeInterface
    {
        return $this->scheduledFor;
    }

    public function setScheduledFor(?\DateTimeInterface $scheduledFor): static
    {
        $this->scheduledFor = $scheduledFor;
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
