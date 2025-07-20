<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\Table(name: 'candidature')]
#[ApiResource(
    uriTemplate: '/candidatures',
    operations: [
        new Get(
            uriTemplate: '/candidatures/{id}',
            normalizationContext: ['groups' => ['candidature:read']]
        ),
        new GetCollection(
            uriTemplate: '/candidatures',
            normalizationContext: ['groups' => ['candidature:read']]
        ),
        new Post(
            uriTemplate: '/candidatures',
            normalizationContext: ['groups' => ['candidature:read']],
            denormalizationContext: ['groups' => ['candidature:write']]
        ),
        new Put(
            uriTemplate: '/candidatures/{id}',
            normalizationContext: ['groups' => ['candidature:read']],
            denormalizationContext: ['groups' => ['candidature:write']]
        ),
        new Patch(
            uriTemplate: '/candidatures/{id}',
            normalizationContext: ['groups' => ['candidature:read']],
            denormalizationContext: ['groups' => ['candidature:write']]
        ),
        new Delete(
            uriTemplate: '/candidatures/{id}'
        )
    ]
)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['candidature:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, name: 'titre_poste')]
    #[Groups(['candidature:read', 'candidature:write'])]
    #[Assert\NotBlank]
    private ?string $titrePoste = null;

    #[ORM\Column(length: 255)]
    #[Groups(['candidature:read', 'candidature:write'])]
    #[Assert\NotBlank]
    private ?string $entreprise = null;

    #[ORM\Column(length: 50)]
    #[Groups(['candidature:read', 'candidature:write'])]
    #[Assert\Choice(choices: ['a_faire', 'en_attente', 'relance', 'entretien', 'accepte', 'refuse'])]
    private ?string $statut = 'a_faire';

    #[ORM\Column(type: 'date', name: 'date_depot')]
    #[Groups(['candidature:read', 'candidature:write'])]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateDepot = null;

    #[ORM\Column(type: 'date', nullable: true, name: 'date_entretien')]
    #[Groups(['candidature:read', 'candidature:write'])]
    private ?\DateTimeInterface $dateEntretien = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true, name: 'heure_entretien')]
    #[Groups(['candidature:read', 'candidature:write'])]
    private ?string $heureEntretien = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidature:read', 'candidature:write'])]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true, name: 'job_id')]
    #[Groups(['candidature:read', 'candidature:write'])]
    private ?string $jobId = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['candidature:read', 'candidature:write'])]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', name: 'date_creation')]
    #[Groups(['candidature:read'])]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitrePoste(): ?string
    {
        return $this->titrePoste;
    }

    public function setTitrePoste(string $titrePoste): static
    {
        $this->titrePoste = $titrePoste;
        return $this;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(string $entreprise): static
    {
        $this->entreprise = $entreprise;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateDepot(): ?\DateTimeInterface
    {
        return $this->dateDepot;
    }

    public function setDateDepot(\DateTimeInterface $dateDepot): static
    {
        $this->dateDepot = $dateDepot;
        return $this;
    }

    public function getDateEntretien(): ?\DateTimeInterface
    {
        return $this->dateEntretien;
    }

    public function setDateEntretien(?\DateTimeInterface $dateEntretien): static
    {
        $this->dateEntretien = $dateEntretien;
        return $this;
    }

    public function getHeureEntretien(): ?string
    {
        return $this->heureEntretien;
    }

    public function setHeureEntretien(?string $heureEntretien): static
    {
        $this->heureEntretien = $heureEntretien;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getJobId(): ?string
    {
        return $this->jobId;
    }

    public function setJobId(?string $jobId): static
    {
        $this->jobId = $jobId;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
}
