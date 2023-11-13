<?php

namespace App\Entity;

use App\Repository\RapportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapportRepository::class)]
#[ORM\Table(name: '`men_rapports`')]
class Rapport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $activite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $activiteFichier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionFichier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultatFichier = null;

    #[ORM\ManyToOne(inversedBy: 'rapports')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'rapports')]
    private ?College $college = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?bool $isDeleted = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $fichier = [];


    public function __construct()
    {
        // Set the createdAt value when an instance is created
        $this->createdAt = new \DateTimeImmutable();
        $this->isDeleted = false;
    }

    public function getFichier(): array
    {
        if ($this->fichier == null) {
            return [];
        }
        return $this->fichier;
    }

    public function SizeFichier(): int
    {
        return count($this->fichier);
    }

    public function setFichier(?array $fichier): self
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function   __toString()
    {
        return $this->getDescription();
    }


    public function getActivite(): ?string
    {
        return $this->activite;
    }

    public function setActivite(?string $activite): static
    {
        $this->activite = $activite;

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

    public function getResultat(): ?string
    {
        return $this->resultat;
    }

    public function setResultat(?string $resultat): static
    {
        $this->resultat = $resultat;

        return $this;
    }

    public function getActiviteFichier(): ?string
    {
        return $this->activiteFichier;
    }

    public function setActiviteFichier(?string $activiteFichier): static
    {
        $this->activiteFichier = $activiteFichier;

        return $this;
    }

    public function getDescriptionFichier(): ?string
    {
        return $this->descriptionFichier;
    }

    public function setDescriptionFichier(?string $descriptionFichier): static
    {
        $this->descriptionFichier = $descriptionFichier;

        return $this;
    }

    public function getResultatFichier(): ?string
    {
        return $this->resultatFichier;
    }

    public function setResultatFichier(?string $resultatFichier): static
    {
        $this->resultatFichier = $resultatFichier;

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

    public function getCollege(): ?College
    {
        return $this->college;
    }

    public function setCollege(?College $college): static
    {
        $this->college = $college;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
