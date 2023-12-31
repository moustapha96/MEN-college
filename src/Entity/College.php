<?php

namespace App\Entity;

use App\Repository\CollegeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollegeRepository::class)]
#[ORM\Table(name: '`men_colleges`')]
class College
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'college', targetEntity: Rapport::class)]
    private Collection $rapports;

    #[ORM\OneToMany(mappedBy: 'college', targetEntity: User::class)]
    private Collection $user;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rapport = null;


    public function __construct()
    {
        $this->rapports = new ArrayCollection();
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

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

    /**
     * @return Collection<int, Rapport>
     */
    public function getRapports(): Collection
    {
        return $this->rapports;
    }

    public function addRapport(Rapport $rapport): static
    {
        if (!$this->rapports->contains($rapport)) {
            $this->rapports->add($rapport);
            $rapport->setCollege($this);
        }

        return $this;
    }

    public function getRapportsByMonth($mois)
    {
        $rapportsDuMois = [];

        foreach ($this->rapports as $rapport) {
            // Récupère le mois du rapport
            $moisRapport = $rapport->getCreatedAt()->format('n');

            // Vérifie si le rapport est du mois en cours
            if ($moisRapport == $mois) {
                $rapportsDuMois[] = $rapport;
            }
        }

        return $rapportsDuMois;
    }

    public function SizeRapport(): int
    {

        return count($this->getRapports());
    }

    public function removeRapport(Rapport $rapport): static
    {
        if ($this->rapports->removeElement($rapport)) {
            // set the owning side to null (unless already changed)
            if ($rapport->getCollege() === $this) {
                $rapport->setCollege(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setCollege($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCollege() === $this) {
                $user->setCollege(null);
            }
        }

        return $this;
    }

    public function getRapport(): ?string
    {
        return $this->rapport;
    }

    public function setRapport(?string $rapport): static
    {
        $this->rapport = $rapport;

        return $this;
    }
}
