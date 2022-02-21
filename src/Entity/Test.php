<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TestRepository::class)
 */
class Test
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *  @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $q1;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $r1;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $q2;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $r2;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $q3;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $r3;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $q4;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $r4;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $q5;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups("post:read")
     */
    private $r5;

    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="tests")
     */
    private $recruteur;

    /**
     * @ORM\OneToOne(targetEntity=Certificat::class, mappedBy="test", cascade={"persist", "remove"})
     */
    private $certificat;

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

    public function getQ1(): ?string
    {
        return $this->q1;
    }

    public function setQ1(string $q1): self
    {
        $this->q1 = $q1;

        return $this;
    }

    public function getR1(): ?string
    {
        return $this->r1;
    }

    public function setR1(string $r1): self
    {
        $this->r1 = $r1;

        return $this;
    }

    public function getQ2(): ?string
    {
        return $this->q2;
    }

    public function setQ2(string $q2): self
    {
        $this->q2 = $q2;

        return $this;
    }

    public function getR2(): ?string
    {
        return $this->r2;
    }

    public function setR2(string $r2): self
    {
        $this->r2 = $r2;

        return $this;
    }

    public function getQ3(): ?string
    {
        return $this->q3;
    }

    public function setQ3(string $q3): self
    {
        $this->q3 = $q3;

        return $this;
    }

    public function getR3(): ?string
    {
        return $this->r3;
    }

    public function setR3(string $r3): self
    {
        $this->r3 = $r3;

        return $this;
    }

    public function getQ4(): ?string
    {
        return $this->q4;
    }

    public function setQ4(string $q4): self
    {
        $this->q4 = $q4;

        return $this;
    }

    public function getR4(): ?string
    {
        return $this->r4;
    }

    public function setR4(string $r4): self
    {
        $this->r4 = $r4;

        return $this;
    }

    public function getQ5(): ?string
    {
        return $this->q5;
    }

    public function setQ5(string $q5): self
    {
        $this->q5 = $q5;

        return $this;
    }

    public function getR5(): ?string
    {
        return $this->r5;
    }

    public function setR5(string $r5): self
    {
        $this->r5 = $r5;

        return $this;
    }

    public function getRecruteur(): ?Recruteur
    {
        return $this->recruteur;
    }

    public function setRecruteur(?Recruteur $recruteur): self
    {
        $this->recruteur = $recruteur;

        return $this;
    }

    public function getCertificat(): ?Certificat
    {
        return $this->certificat;
    }

    public function setCertificat(?Certificat $certificat): self
    {
        // unset the owning side of the relation if necessary
        if ($certificat === null && $this->certificat !== null) {
            $this->certificat->setTest(null);
        }

        // set the owning side of the relation if necessary
        if ($certificat !== null && $certificat->getTest() !== $this) {
            $certificat->setTest($this);
        }

        $this->certificat = $certificat;

        return $this;
    }

}
