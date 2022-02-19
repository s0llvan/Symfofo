<?php

namespace App\Entity;

use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks as HasLifecycleCallbacks;

/**
* @ORM\Entity(repositoryClass=TopicRepository::class)
* @HasLifecycleCallbacks
*/
class Topic
{
	/**
	* @ORM\Id
	* @ORM\GeneratedValue
	* @ORM\Column(type="integer")
	*/
	private $id;
	
	/**
	* @ORM\ManyToOne(targetEntity=Category::class, inversedBy="topics")
	* @ORM\JoinColumn(nullable=false)
	*/
	private $category;
	
	/**
	* @ORM\Column(type="text")
	*/
	private $message;
	
	/**
	* @ORM\Column(type="datetime_immutable")
	*/
	private $createdAt;
	
	/**
	* @ORM\Column(type="datetime_immutable")
	*/
	private $updatedAt;
	
	/**
	* @ORM\Column(type="string", length=255)
	*/
	private $title;
	
	/**
	* @ORM\OneToMany(targetEntity=Post::class, mappedBy="topic", orphanRemoval=true)
	*/
	private $posts;
	
	/**
	* @ORM\Column(type="bigint")
	*/
	private $views;
	
	/**
	* @ORM\ManyToOne(targetEntity=User::class, inversedBy="topics")
	*/
	private $author;
	
	public function __construct()
	{
		$this->posts = new ArrayCollection();
		$this->views = 0;
	}
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getCategory(): ?Category
	{
		return $this->category;
	}
	
	public function setCategory(?Category $category): self
	{
		$this->category = $category;
		
		return $this;
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
	
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	public function setTitle(string $title): self
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	* @return Collection|Post[]
	*/
	public function getPosts(): Collection
	{
		return $this->posts;
	}
	
	public function addPost(Post $post): self
	{
		if (!$this->posts->contains($post)) {
			$this->posts[] = $post;
			$post->setTopic($this);
		}
		
		return $this;
	}
	
	public function removePost(Post $post): self
	{
		if ($this->posts->removeElement($post)) {
			// set the owning side to null (unless already changed)
			if ($post->getTopic() === $this) {
				$post->setTopic(null);
			}
		}
		
		return $this;
	}
	
	/**
	* @ORM\PrePersist
	* @ORM\PreUpdate
	*/
	public function updatedTimestamps(): void
	{
		$this->setUpdatedAt(new \DateTimeImmutable('now'));    
		if ($this->getCreatedAt() === null) {
			$this->setCreatedAt(new \DateTimeImmutable('now'));
		}
	}
	
	public function getViews(): ?string
	{
		return $this->views;
	}
	
	public function setViews(string $views): self
	{
		$this->views = $views;
		
		return $this;
	}
	
	public function getLastPost(): ?Post
	{
		return $this->posts->count() ? $this->posts->last() : null;
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
