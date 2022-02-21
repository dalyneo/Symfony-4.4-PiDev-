<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CategorieRepository::class)
 *
 */
class Categorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="this field is required ")
     * @Groups("post:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="this field is required ")
     * @Groups("post:read")
     */
    private $help;

    /**
     * @ORM\OneToMany(targetEntity=Offre::class, mappedBy="idcategoriy")
     */
    private $idoffre;

    public function __construct()
    {
        $this->idoffre = new ArrayCollection();
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

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(string $help): self
    {
        $this->help = $help;

        return $this;
    }

    /**
     * @return Collection|Offre[]
     */
    public function getIdoffre(): Collection
    {
        return $this->idoffre;
    }

    public function addIdoffre(Offre $idoffre): self
    {
        if (!$this->idoffre->contains($idoffre)) {
            $this->idoffre[] = $idoffre;
            $idoffre->setIdcategoriy($this);
        }

        return $this;
    }

    public function removeIdoffre(Offre $idoffre): self
    {
        if ($this->idoffre->removeElement($idoffre)) {
            // set the owning side to null (unless already changed)
            if ($idoffre->getIdcategoriy() === $this) {
                $idoffre->setIdcategoriy(null);
            }
        }

        return $this;
    }

}
