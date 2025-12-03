<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[MongoDB\Document(collection: 'posts')]
class Post
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $title = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $content = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $createdAt = null;

    // Relation vers l'utilisateur (Auteur)
    // storeAs: 'id' signifie qu'on ne stocke que l'ID dans MongoDB, mais Symfony chargera tout l'objet User pour nous.
    #[MongoDB\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private ?User $author = null;

    // Relation inverse : Un post peut avoir plusieurs commentaires
    // mappedBy signifie que c'est le Commentaire qui porte la relation
    #[MongoDB\ReferenceMany(targetDocument: Comment::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $comments;

    public function __construct()
    {
        // La date est mise automatiquement à "maintenant" lors de la création
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): self { $this->author = $author; return $this; }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection { return $this->comments; }
}