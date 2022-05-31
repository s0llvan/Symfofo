<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks as HasLifecycleCallbacks;

#[ORM\Entity(repositoryClass:PostRepository::class)]
#[HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private $id;
    
    #[ORM\Column(type:'text')]
    private $message;
    
    #[ORM\ManyToOne(targetEntity:Topic::class, inversedBy:"posts")]
    #[ORM\JoinColumn(nullable:false)]
    private $topic;
    
    #[ORM\Column(type:'datetime_immutable')]
    private $createdAt;
    
    #[ORM\Column(type:'datetime_immutable')]
    private $updatedAt;
    
    #[ORM\ManyToOne(targetEntity:User::class, inversedBy:"posts")]
    private $author;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getMessage(): ?string
    {
        return $this->message;
    }
    
    public function setMessage(string $message): self
    {
        $this->message = $message;
        
        return $this;
    }
    
    public function getTopic(): ?Topic
    {
        return $this->topic;
    }
    
    public function setTopic(?Topic $topic): self
    {
        $this->topic = $topic;
        
        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
    
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTimeImmutable('now'));    
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable('now'));
        }
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
}
