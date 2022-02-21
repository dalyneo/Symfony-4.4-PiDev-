<?php

namespace App\Entity;

use App\Repository\RecruteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=RecruteurRepository::class)
 */
class Recruteur
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
     * @Groups("post:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomsociete;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $mail;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Length(min="8",minMessage="Votre numero doit faire min 8 char")
     * @Groups("post:read")
     */
    private $numsociete;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $mdp;

    /**
     * @ORM\OneToMany(targetEntity=Offre::class, mappedBy="idrecruteur")
     */
    private $idoffre;
    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="idrecruteur")
     */
    private $idcomment;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Postuler::class, mappedBy="recruteur")
     */
    private $likes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $competence;

    /**
     * @ORM\OneToMany(targetEntity=Forum::class, mappedBy="recruteur")
     */
    private $forums;

    /**
     * @ORM\OneToMany(targetEntity=Commenter::class, mappedBy="recruteur")
     */
    private $commenters;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="user")
     */
    private $projets;
    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="recruteur")
     */
    private $tests;

    /**
     * @ORM\OneToMany(targetEntity=Certificat::class, mappedBy="idrecruteur")
     */
    private $idcertificat;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("post:read")
     */

    private $prix;
    /**
     * @ORM\OneToMany(targetEntity=Reclamation::class, mappedBy="recruteur")
     * @Groups("recruteur")
     */
    private $reclamations;
    public function __construct()
    {
        $this->offre = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->forums = new ArrayCollection();
        $this->commenters = new ArrayCollection();
        $this->projets = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->idcertificat = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNomsociete(): ?string
    {
        return $this->nomsociete;
    }

    public function setNomsociete(string $nomsociete): self
    {
        $this->nomsociete = $nomsociete;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getNumsociete(): ?int
    {
        return $this->numsociete;
    }

    public function setNumsociete(int $numsociete): self
    {
        $this->numsociete = $numsociete;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;

        return $this;
    }
    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setRecruteur($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getRecruteur() === $this) {
                $test->setRecruteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Certificat[]
     */
    public function getIdcertificat(): Collection
    {
        return $this->idcertificat;
    }

    public function addIdcertificat(Certificat $idcertificat): self
    {
        if (!$this->idcertificat->contains($idcertificat)) {
            $this->idcertificat[] = $idcertificat;
            $idcertificat->setIdrecruteur($this);
        }

        return $this;
    }

    public function removeIdcertificat(Certificat $idcertificat): self
    {
        if ($this->idcertificat->removeElement($idcertificat)) {
            // set the owning side to null (unless already changed)
            if ($idcertificat->getIdrecruteur() === $this) {
                $idcertificat->setIdrecruteur(null);
            }
        }

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
            if ($idoffre->getIdrecruteur() === $this) {
                $idoffre->setIdrecruteur(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|Comment[]
     */
    public function getIdcomment(): Collection
    {
        return $this->idcomment;
    }

    public function addIdcomment(Comment $idcomment): self
    {
        if (!$this->idoffre->contains($idcomment)) {
            $this->idoffre[] = $idcomment;
            $idcomment->setIdcategoriy($this);
        }

        return $this;
    }

    public function removeIdcomment(Comment $idcomment): self
    {
        if ($this->idcomment->removeElement($idcomment)) {
            // set the owning side to null (unless already changed)
            if ($idcomment->getIdrecruteur() === $this) {
                $idcomment->setIdrecruteur(null);
            }
        }

        return $this;
    }
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Postuler[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Postuler $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setRecruteur($this);
        }

        return $this;
    }

    public function removeLike(Postuler $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getRecruteur() === $this) {
                $like->setRecruteur(null);
            }
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCompetence(): ?string
    {
        return $this->competence;
    }

    public function setCompetence(?string $competence): self
    {
        $this->competence = $competence;

        return $this;
    }

    /**
     * @return Collection|Forum[]
     */
    public function getForums(): Collection
    {
        return $this->forums;
    }

    public function addForum(Forum $forum): self
    {
        if (!$this->forums->contains($forum)) {
            $this->forums[] = $forum;
            $forum->setRecruteur($this);
        }

        return $this;
    }

    public function removeForum(Forum $forum): self
    {
        if ($this->forums->removeElement($forum)) {
            // set the owning side to null (unless already changed)
            if ($forum->getRecruteur() === $this) {
                $forum->setRecruteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Commenter[]
     */
    public function getCommenters(): Collection
    {
        return $this->commenters;
    }

    public function addCommenter(Commenter $commenter): self
    {
        if (!$this->commenters->contains($commenter)) {
            $this->commenters[] = $commenter;
            $commenter->setRecruteur($this);
        }

        return $this;
    }

    public function removeCommenter(Commenter $commenter): self
    {
        if ($this->commenters->removeElement($commenter)) {
            // set the owning side to null (unless already changed)
            if ($commenter->getRecruteur() === $this) {
                $commenter->setRecruteur(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|Projet[]
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setUser($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getUser() === $this) {
                $projet->setUser(null);
            }
        }

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }
    /**
     * @return Collection|Reclamation[]
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations[] = $reclamation;
            $reclamation->setRecruteur($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        if ($this->reclamations->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getRecruteur() === $this) {
                $reclamation->setRecruteur(null);
            }
        }

        return $this;
    }
}
