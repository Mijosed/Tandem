<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use App\Entity\Job;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['application:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['application:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['application:read']],
            denormalizationContext: ['groups' => ['application:write']]
        ),
        new Put(
            normalizationContext: ['groups' => ['application:read']],
            denormalizationContext: ['groups' => ['application:write']]
        ),
        new Delete()
    ]
)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['application:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Job::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application:read'])]
    private ?Job $job = null;

    #[ORM\Column(type: 'datetime', name: 'applied_at')]
    #[Groups(['application:read', 'application:write'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $appliedAt = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'cover_letter')]
    #[Groups(['application:read', 'application:write'])]
    private ?string $coverLetter = null;

    #[ORM\Column(length: 255, nullable: true, name: 'resume_path')]
    #[Groups(['application:read', 'application:write'])]
    private ?string $resumePath = null;

    #[ORM\Column(length: 50)]
    #[Groups(['application:read', 'application:write'])]
    #[Assert\Choice(choices: ['pending', 'followed_up', 'interview', 'rejected', 'accepted'])]
    private ?string $status = 'pending';

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Groups(['application:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->appliedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): static
    {
        $this->job = $job;
        return $this;
    }

    public function getAppliedAt(): ?\DateTimeInterface
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(\DateTimeInterface $appliedAt): static
    {
        $this->appliedAt = $appliedAt;
        return $this;
    }

    public function getCoverLetter(): ?string
    {
        return $this->coverLetter;
    }

    public function setCoverLetter(?string $coverLetter): static
    {
        $this->coverLetter = $coverLetter;
        return $this;
    }

    public function getResumePath(): ?string
    {
        return $this->resumePath;
    }

    public function setResumePath(?string $resumePath): static
    {
        $this->resumePath = $resumePath;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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
