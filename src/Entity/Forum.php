<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ForumRepository::class)
 */
class Forum
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
     *@Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "votre sujet doit comporter au minimum {{ limit }} characters",
     *      maxMessage = "votre sujet ne doit pas depasser {{ limit }} characters",
     *      allowEmptyString = false
     *     )
     * @Assert\NotBlank
     * @Groups("post:read")
     */
    private $sujet;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups("post:read")
     */
    private $probleme;




    /**
     * @ORM\OneToMany(targetEntity=Commenter::class, mappedBy="forum", orphanRemoval=true)
     */
    private $commenters;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="forums")
     * @Groups("recruteur")
     */
    private $recruteur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $theme;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $users_ids_views;

    public function __construct()
    {
        $this->commenters = new ArrayCollection();
        $this->date = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): self
    {
        $this->sujet = $sujet;

        return $this;
    }

    public function getProbleme(): ?string
    {
        return $this->probleme;
    }

    public function setProbleme(string $probleme): self
    {
        $this->probleme = $probleme;

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
            $commenter->setForum($this);
        }

        return $this;
    }

    public function removeCommenter(Commenter $commenter): self
    {
        if ($this->commenters->removeElement($commenter)) {
            // set the owning side to null (unless already changed)
            if ($commenter->getForum() === $this) {
                $commenter->setForum(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getUsersIdsViews(): ?string
    {
        return $this->users_ids_views;
    }

    public function setUsersIdsViews(?string $users_ids_views): self
    {
        $this->users_ids_views = $users_ids_views;

        return $this;
    }
}
