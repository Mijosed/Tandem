<?php

namespace App\Entity;

use App\Repository\ScheduleEventRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduleEventRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['schedule:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['schedule:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['schedule:read']],
            denormalizationContext: ['groups' => ['schedule:write']]
        ),
        new Put(
            normalizationContext: ['groups' => ['schedule:read']],
            denormalizationContext: ['groups' => ['schedule:write']]
        ),
        new Delete()
    ]
)]
class ScheduleEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['schedule:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['schedule:read', 'schedule:write'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['schedule:read', 'schedule:write'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['schedule:read', 'schedule:write'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 100)]
    #[Groups(['schedule:read', 'schedule:write'])]
    #[Assert\Choice(choices: ['interview', 'meeting', 'reminder', 'deadline', 'personal'])]
    private ?string $type = 'personal';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?string $location = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['schedule:read', 'schedule:write'])]
    private bool $allDay = false;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'scheduleEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Candidature::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?Candidature $Candidature = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['schedule:read'])]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): static
    {
        $this->allDay = $allDay;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
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

    public function getCandidature(): ?Candidature
    {
        return $this->Candidature;
    }

    public function setCandidature(?Candidature $Candidature): static
    {
        $this->Candidature = $Candidature;
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
