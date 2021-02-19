<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GroupeRepository::class)
 */
class Groupe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner un nom pour le groupe", groups={"GroupeType"})
     * @ORM\Column(type="string", length=100)
     */
    private $Nom;

    /**
     * @ORM\ManyToMany(targetEntity=Participant::class, inversedBy="groupes")
     */
    private $Membres;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="myGroupes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Proprietaire;

    public function __construct()
    {
        $this->Membres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getMembres(): Collection
    {
        return $this->Membres;
    }

    public function addMembre(Participant $membre): self
    {
        if (!$this->Membres->contains($membre)) {
            $this->Membres[] = $membre;
        }

        return $this;
    }

    public function removeMembre(Participant $membre): self
    {
        $this->Membres->removeElement($membre);

        return $this;
    }

    public function getProprietaire(): ?Participant
    {
        return $this->Proprietaire;
    }

    public function setProprietaire(?Participant $Proprietaire): self
    {
        $this->Proprietaire = $Proprietaire;

        return $this;
    }
}
