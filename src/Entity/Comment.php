<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
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
    private $authorName;

    /**
     * @ORM\Column(type="text")
     * @Groups("post:read")
     */
    private $content;
    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $createdAt;
    /**
     * @ORM\ManyToOne(targetEntity=Offre::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offre;
    /**
     * @ORM\ManyToOne(targetEntity=Recruteur::class, inversedBy="idcomment")
     */
    private $idrecruteur;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): self
    {
        $this->offre = $offre;

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
