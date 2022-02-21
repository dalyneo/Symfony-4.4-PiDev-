<?php

namespace App\Entity;

use App\Repository\CatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CatRepository::class)
 */
class Cat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("cat:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("cat:read")
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Evenement::class, mappedBy="idcat")
     */
    private $idevenement;

    public function __construct()
    {
        $this->idevenement = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|Evenement[]
     */
    public function getIdevenement(): Collection
    {
        return $this->idevenement;
    }

    public function addIdevenement(Evenement $idevenement): self
    {
        if (!$this->idevenement->contains($idevenement)) {
            $this->idevenement[] = $idevenement;
            $idevenement->setIdcat($this);
        }

        return $this;
    }

    public function removeIdevenemnt(Evenement $idevenement): self
    {
        if ($this->idevenement->removeElement($idevenement)) {
            // set the owning side to null (unless already changed)
            if ($idevenement->getIdcat() === $this) {
                $idevenement->setIdcat(null);
            }
        }

        return $this;
    }
}
