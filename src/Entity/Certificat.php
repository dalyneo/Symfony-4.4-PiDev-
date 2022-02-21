<?php

namespace App\Entity;

use App\Repository\CertificatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CertificatRepository::class)
 */
class Certificat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToOne(targetEntity=Test::class, inversedBy="certificat", cascade={"persist", "remove"})
     */
    private $test;

    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="idcertificat")
     */
    private $idrecruteur;



    public function __construct()
    {

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

    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(?Test $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getIdrecruteur(): ?Recruteur
    {
        return $this->idrecruteur;
    }

    public function setIdrecruteur(?Recruteur $idrecruteur): self
    {
        $this->idrecruteur = $idrecruteur;

        return $this;
    }



}
