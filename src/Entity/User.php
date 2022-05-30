<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
* @ORM\Entity(repositoryClass="App\Repository\UserRepository")
* @ORM\Table(name="user")
* @UniqueEntity(fields="email", message="Email address already used !")
* @UniqueEntity(fields="username", message="Username already used !")
*/
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	/**
	* @ORM\Id
	* @ORM\GeneratedValue
	* @ORM\Column(type="integer")
	*/
	private $id;
	
	/**
	* @ORM\Column(type="string", length=255, nullable=true)
	*/
	private $emailConfirmationToken;
	
	/**
	* @ORM\Column(type="boolean")
	*/
	private $emailConfirmed;
	
	/**
	* @ORM\ManyToOne(targetEntity=Role::class, inversedBy="users")
	* @ORM\JoinColumn(nullable=false)
	*/
	private $role;
	
	/**
	* @var string
	*
	* @ORM\Column(type="string", unique=true)
	* @Assert\NotBlank(message="Username is not required")
	* @Assert\Length(
		*       min = 3,
		*       minMessage = "Username is too short. It should have {{ limit }} characters or more.",
		*       max = 12,
		*       maxMessage = "Username is too long. It should have {{ limit }} characters or less."
		* )
		*/
		private $username;
		
		/**
		* @var string
		*
		* @ORM\Column(type="string", unique=true)
		* @Assert\NotBlank(message="Email is required.")
		* @Assert\Email(message="Email is not valid.")
		*/
		private $email;
		
		/**
		* @var string
		*
		* @ORM\Column(type="string", length=64)
		* @Assert\NotBlank(message="Password is required.")
		* @Assert\Length(
			*       min = 8,
			*       minMessage = "Password is too short. It should have {{ limit }} characters or more.",
			*       max = 64,
			*       maxMessage = "Password is too long. It should have {{ limit }} characters or less."
			* )
			*/
			private $password;
			
			/**
			* @ORM\Column(type="string", length=255, nullable=true)
			*/
			private $passwordResetToken;
			
			/**
			* @ORM\Column(type="datetime", nullable=true)
			*/
			private $passwordResetTokenLast;
			
			/**
			* @ORM\OneToMany(targetEntity=Topic::class, mappedBy="author")
			*/
			private $topics;
			
			/**
			* @ORM\OneToMany(targetEntity=Post::class, mappedBy="author")
			*/
			private $posts;
			
			public function __construct()
			{
				$this->emailConfirmed = false;
				$this->posts = new ArrayCollection();
				$this->topics = new ArrayCollection();
			}
			
			public function getId(): ?int
			{
				return $this->id;
			}
			
			public function getEmailConfirmationToken(): ?string
			{
				return $this->emailConfirmationToken;
			}
			
			public function setEmailConfirmationToken(?string $emailConfirmationToken): self
			{
				$this->emailConfirmationToken = $emailConfirmationToken;
				
				return $this;
			}
			
			public function getEmailConfirmed(): ?bool
			{
				return $this->emailConfirmed;
			}
			
			public function setEmailConfirmed(bool $emailConfirmed): self
			{
				$this->emailConfirmed = $emailConfirmed;
				
				return $this;
			}
			
			public function getRole(): ?Role
			{
				return $this->role;
			}
			
			public function getRoles(): array
			{
				$slug = 'ROLE_USER';
				
				if ($this->getRole()) {
					$slug = $this->getRole()->getSlug();
				}
				
				return [$slug];
			}
			
			public function setRole(?Role $role): self
			{
				$this->role = $role;
				
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
			
			public function getPassword(): ?string
			{
				return $this->password;
			}
			
			public function setPassword(string $password): self
			{
				$this->password = $password;
				
				return $this;
			}
			
			public function getUsername(): ?string
			{
				return $this->username;
			}
			
			public function setUsername(string $username): self
			{
				$this->username = $username;
				
				return $this;
			}
			
			/**
			* Retour le salt qui a servi à coder le mot de passe
			*
			* {@inheritdoc}
			*/
			public function getSalt(): ?string
			{
				// See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
				// we're using bcrypt in security.yml to encode the password, so
				// the salt value is built-in and you don't have to generate one
				
				return null;
			}
			
			/**
			* Removes sensitive data from the user.
			*
			* {@inheritdoc}
			*/
			public function eraseCredentials(): void
			{
				// Nous n'avons pas besoin de cette methode car nous n'utilions pas de plainPassword
				// Mais elle est obligatoire car comprise dans l'interface UserInterface
				// $this->plainPassword = null;
			}
			
			public function getUserIdentifier(): string
			{
				return $this->username;
			}
			
			public function getPasswordResetToken(): ?string
			{
				return $this->passwordResetToken;
			}
			
			public function setPasswordResetToken(?string $passwordResetToken): self
			{
				$this->passwordResetToken = $passwordResetToken;
				
				return $this;
			}
			
			public function getPasswordResetTokenLast(): ?\DateTimeInterface
			{
				return $this->passwordResetTokenLast;
			}
			
			public function setPasswordResetTokenLast(?\DateTimeInterface $passwordResetTokenLast): self
			{
				$this->passwordResetTokenLast = $passwordResetTokenLast;
				
				return $this;
			}
			
			/**
			* @return Collection|Topic[]
			*/
			public function getTopics(): Collection
			{
				return $this->topics;
			}
			
			public function addTopic(Topic $topic): self
			{
				if (!$this->topics->contains($topic)) {
					$this->topics[] = $topic;
					$topic->setAuthor($this);
				}
				
				return $this;
			}
			
			public function removeTopic(Topic $topic): self
			{
				if ($this->topics->removeElement($topic)) {
					// set the owning side to null (unless already changed)
					if ($topic->getAuthor() === $this) {
						$topic->setAuthor(null);
					}
				}
				
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
					$post->setAuthor($this);
				}
				
				return $this;
			}
			
			public function removePost(Post $post): self
			{
				if ($this->posts->removeElement($post)) {
					// set the owning side to null (unless already changed)
					if ($post->getAuthor() === $this) {
						$post->setAuthor(null);
					}
				}
				
				return $this;
			}
		}
		