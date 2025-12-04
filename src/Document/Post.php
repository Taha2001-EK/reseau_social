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

    #[MongoDB\ReferenceOne(targetDocument: User::class, storeAs: 'id')]
    private ?User $author = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTimeInterface $createdAt = null;

    #[MongoDB\ReferenceMany(targetDocument: Comment::class, mappedBy: 'post', cascade: ['remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
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

    public function getComments(): Collection
    {
        return $this->comments;
    }
}
