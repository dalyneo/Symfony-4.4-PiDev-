<?php

namespace App\Entity;
use App\Entity\Recruteur;
use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use http\Env\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OffreRepository::class)
 *
 */
class Offre
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
     * @Assert\NotBlank(message=" this field is required ")
     * @Groups("post:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message=" this field should be a valid email ")
     * @Groups("post:read")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255)
     *@Assert\NotBlank(message=" this field is required ")
     * @Groups("post:read")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message=" this field is required ")
     * @Groups("post:read")
     */
    private $description;
    protected $captchaCode;
    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class, inversedBy="idoffre")
     */
    private $idcategoriy;
    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="idoffre")
     */
    private $idrecruteur;


    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $abn;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="offre")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Postuler::class, mappedBy="offre")
     */
    private $likes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getIdcategoriy(): ?Categorie
    {
        return $this->idcategoriy;
    }

    public function setIdcategoriy(?Categorie $idcategoriy): self
    {
        $this->idcategoriy = $idcategoriy;
        return $this;
    }
    public function getCaptchaCode()
    {
        return $this->captchaCode;
    }

    public function setCaptchaCode($captchaCode)
    {
        $this->captchaCode = $captchaCode;
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
    public function getAbn(): ?int
    {
        return $this->abn;
    }
    public function setAbn(int $abn): self
    {
        $this->abn = $abn;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setOffre($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getOffre() === $this) {
                $comment->setOffre(null);
            }
        }

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
            $like->setOffre($this);
        }

        return $this;
    }

    public function removeLike(Postuler $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getOffre() === $this) {
                $like->setOffre(null);
            }
        }

        return $this;
    }

    public function isLikedByRecruteur(Recruteur $recruteur) : bool
    {
       foreach ($this->likes as $like)
       {
           if($like->getRecruteur()==$recruteur)
               return true;
       }
       return false ;
    }
}
