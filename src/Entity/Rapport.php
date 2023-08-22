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


    public function __construct()
    {
        // Set the createdAt value when an instance is created
        $this->createdAt = new \DateTimeImmutable();
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
}
