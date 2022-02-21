<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 */
class Projet
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
    private $nomProjet;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $projetDescription;



    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jobType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jobSalary;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jobTime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo;

    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProjet(): ?string
    {
        return $this->nomProjet;
    }

    public function setNomProjet(string $nomProjet): self
    {
        $this->nomProjet = $nomProjet;

        return $this;
    }

    public function getProjetDescription(): ?string
    {
        return $this->projetDescription;
    }

    public function setProjetDescription(string $projetDescription): self
    {
        $this->projetDescription = $projetDescription;

        return $this;
    }



    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function setJobType(string $jobType): self
    {
        $this->jobType = $jobType;

        return $this;
    }

    public function getJobSalary(): ?string
    {
        return $this->jobSalary;
    }

    public function setJobSalary(string $jobSalary): self
    {
        $this->jobSalary = $jobSalary;

        return $this;
    }

    public function getJobTime(): ?string
    {
        return $this->jobTime;
    }

    public function setJobTime(string $jobTime): self
    {
        $this->jobTime = $jobTime;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUser(): ?Recruteur
    {
        return $this->user;
    }

    public function setUser(?Recruteur $user): self
    {
        $this->user = $user;

        return $this;
    }
}
