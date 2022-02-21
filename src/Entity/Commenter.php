<?php

namespace App\Entity;

use App\Repository\CommenterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CommenterRepository::class)
 */
class Commenter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups("post:read")
     */
    private $commentaire;

    /**
     * @ORM\ManyToOne(targetEntity=Forum::class, inversedBy="commenters")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")
     */
    private $forum;
    /**
     * @Groups("post:read")
     */
    private $forumId ;

    /**
     * @param mixed $forumId
     */
    public function setForumId($forumId): void
    {
        $this->forumId = $forumId;
    }

    /**
     * @return mixed
     */
    public function getForumId()
    {
        return $this->forumId;
    }
    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="commenters")
     * @Groups("post:read")
     */
    private $recruteur;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("post:read")
     */
    private $rating;

    public function getRef(): ?int
    {
        return $this->ref;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): self
    {
        $this->forum = $forum;

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

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }
}
