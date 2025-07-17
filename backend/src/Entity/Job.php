<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['job:read']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['job:read']]
        ),
        new Post(
            normalizationContext: ['groups' => ['job:read']],
            denormalizationContext: ['groups' => ['job:write']]
        ),
        new Put(
            normalizationContext: ['groups' => ['job:read']],
            denormalizationContext: ['groups' => ['job:write']]
        ),
        new Delete()
    ]
)]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['job:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    #[Groups(['job:read', 'job:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 100, name: 'contract_type')]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\Choice(choices: ['Alternance', 'Stage', 'CDI', 'CDD', 'Freelance'])]
    private ?string $type = 'Alternance';

    #[ORM\Column(type: 'integer', nullable: true, name: 'salary_min')]
    #[Groups(['job:read', 'job:write'])]
    private ?int $salaryMin = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'salary_max')]
    #[Groups(['job:read', 'job:write'])]
    private ?int $salaryMax = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['job:read', 'job:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true, name: 'indeed_job_key')]
    #[Groups(['job:read', 'job:write'])]
    private ?string $indeedJobKey = null;

    #[ORM\Column(type: 'boolean', name: 'is_premium')]
    #[Groups(['job:read', 'job:write'])]
    private bool $isPremium = false;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    #[Groups(['job:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    #[Groups(['job:read'])]
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

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSalaryMin(): ?int
    {
        return $this->salaryMin;
    }

    public function setSalaryMin(?int $salaryMin): static
    {
        $this->salaryMin = $salaryMin;
        return $this;
    }

    public function getSalaryMax(): ?int
    {
        return $this->salaryMax;
    }

    public function setSalaryMax(?int $salaryMax): static
    {
        $this->salaryMax = $salaryMax;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIndeedJobKey(): ?string
    {
        return $this->indeedJobKey;
    }

    public function setIndeedJobKey(?string $indeedJobKey): static
    {
        $this->indeedJobKey = $indeedJobKey;
        return $this;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;
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
