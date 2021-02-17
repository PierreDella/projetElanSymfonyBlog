<?php

namespace App\Entity;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Il y a déjà un compte avec cet email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     */
    private $picture;

    /**
     * @ORM\Column(type="datetime")
     * 
     */
    private $dateInscription;

    /**
     * @ORM\OneToMany(targetEntity=Bibliotheque::class, mappedBy="user")
     */
    private $bibliotheques;

    /**
     * @ORM\OneToMany(targetEntity=Recipe::class, mappedBy="user")
     */
    private $recipes;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="subscriber")
     */
    private $listSubscriptions; //Liste des gens que je suis

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="targetUser")
     */
    private $subscribers; // Les gens qui me suivent

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $pseudo;

    /**
     * @ORM\OneToMany(targetEntity=RecipeLike::class, mappedBy="user")
     */
    private $likes;

    

    public function __construct()
    {
        $this->bibliotheques = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->listSubscriptions = new ArrayCollection();
        $this->subscribers = new ArrayCollection();
        $this->dateInscription = new \DateTime();
        $this->likes = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture($picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    /**
     * @return Collection|Bibliotheque[]
     */
    public function getBibliotheques(): Collection
    {
        return $this->bibliotheques;
    }

    public function addBibliotheque(Bibliotheque $bibliotheque): self
    {
        if (!$this->bibliotheques->contains($bibliotheque)) {
            $this->bibliotheques[] = $bibliotheque;
            $bibliotheque->setUser($this);
        }

        return $this;
    }

    public function removeBibliotheque(Bibliotheque $bibliotheque): self
    {
        if ($this->bibliotheques->removeElement($bibliotheque)) {
            // set the owning side to null (unless already changed)
            if ($bibliotheque->getUser() === $this) {
                $bibliotheque->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes[] = $recipe;
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getUser() === $this) {
                $recipe->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getListSubscriptions(): Collection
    {
        return $this->listSubscriptions;
    }

    public function addListSubscription(Subscription $listSubscription): self
    {
        if (!$this->listSubscriptions->contains($listSubscription)) {
            $this->listSubscriptions[] = $listSubscription;
            $listSubscription->setSubscriber($this);
        }

        return $this;
    }

    public function removeListSubscription(Subscription $listSubscription): self
    {
        if ($this->listSubscriptions->removeElement($listSubscription)) {
            // set the owning side to null (unless already changed)
            if ($listSubscription->getSubscriber() === $this) {
                $listSubscription->setSubscriber(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addSubscriber(Subscription $subscriber): self
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers[] = $subscriber;
            $subscriber->setTargetUser($this);
        }

        return $this;
    }

    public function removeSubscriber(Subscription $subscriber): self
    {
        if ($this->subscribers->removeElement($subscriber)) {
            // set the owning side to null (unless already changed)
            if ($subscriber->getTargetUser() === $this) {
                $subscriber->setTargetUser(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isAdmin(){
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * @return Collection|RecipeLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(RecipeLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(RecipeLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }
    /**
     * @return boolean
     */
    public function isSubscribedByUser(User $user) : bool {

        foreach($this->listSubscriptions as $listSubscription){
                // si dans les likes se trouve l'utilisateur ca veut dire qu'il aura liké
            if($listSubscription->getTargetUser() === $user) return true;
        
        }

        return false;
    }
}
