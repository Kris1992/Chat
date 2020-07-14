<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\{UserRepository, FriendRepository};
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"chat:message", "chat:participants", "chat:friends"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"chat:friends"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Groups({"chat:message", "chat:participants", "chat:friends"})
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $agreedTermsAt;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $gender;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastActivity;

    /**
     * @ORM\Column(type="integer")
     */
    private $failedAttempts = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $banTo;

    /**
     * @ORM\OneToOne(targetEntity=PasswordToken::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private $passwordToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"chat:message", "chat:participants", "chat:friends"})
     */
    private $imageFilename;

    /**
     * @ORM\OneToMany(targetEntity=Friend::class, mappedBy="inviter", orphanRemoval=true)
     */
    private $invitedFriends;

    /**
     * @ORM\OneToMany(targetEntity=Friend::class, mappedBy="invitee", orphanRemoval=true)
     */
    private $invitedByFriends;

    public function __construct()
    {
        $this->invitedFriends = new ArrayCollection();
        $this->invitedByFriends = new ArrayCollection();
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

    public function isAdmin(): bool
    {
        if (in_array('ROLE_ADMIN', $this->getRoles())) {
            return true;
        }
        
        return false;
    } 

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
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
        // not needed for apps that do not check user passwords
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function agreeToTerms()
    {
        $this->agreedTermsAt = new \DateTime();
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(\DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function updateLastActivity(): self
    {
        $this->lastActivity = new \DateTime();

        return $this;
    }
    
    /**
     * @Groups({"chat:friends"})
     */
    public function isActiv()
    {
        if ($this->getLastActivity() < new \DateTime('now -30 seconds')) {
            return false;
        }
        
        return true;
    }

    public function getFailedAttempts(): ?int
    {
        return $this->failedAttempts;
    }

    public function increaseFailedAttempts(): int
    {
        $this->failedAttempts = $this->failedAttempts + 1;

        return $this->failedAttempts;
    }

    public function resetFailedAttempts(): self
    {
        $this->failedAttempts = 0;

        return $this;
    }

    public function getBanTo(): ?\DateTimeInterface
    {
        return $this->banTo;
    }

    public function setBanTo(?\DateTimeInterface $banTo): self
    {
        $this->banTo = $banTo;

        return $this;
    }

    public function isBanned(): bool
    {
        if ($this->getBanTo() > new \DateTime()) {
            return true;
        }
        
        return false;
    } 

    public function getPasswordToken(): ?PasswordToken
    {
        return $this->passwordToken;
    }

    public function setPasswordToken(?PasswordToken $passwordToken): self
    {
        $this->passwordToken = $passwordToken;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }
    
    public function getImagePath()
    {
        return ImagesConstants::USERS_IMAGES.'/'.$this->getLogin().'/'.$this->getImageFilename();
    }

    /**
     * @Groups({"chat:message", "chat:participants", "chat:friends"})
     */
    public function getThumbImagePath()
    {
        return ImagesConstants::USERS_IMAGES.'/'.$this->getLogin().'/'.ImagesConstants::THUMB_IMAGES.'/'.$this->getImageFilename();
    }

    /**
     * @return Collection|Friend[]
     */
    public function getInvitedFriends(): Collection
    {
        return $this->invitedFriends;
    }

    /**
     * getInvitedFriend Get friend object if user was invited by given user 
     * @param   User    $user     User object whose is or not invited by given one
     * @return  Collection|Friend
     */
    public function getInvitedFriend(User $user): Collection
    {
        $criteria = FriendRepository::createNotRejectedFriendsByInviteeCriteria($user);

        return $this->invitedFriends->matching($criteria);
    }

    /**
     * @return Collection|Friend[]
     */
    public function getInvitedByFriends(): Collection
    {
        return $this->invitedByFriends;
    }

    /**
     * getInvitedByFriend Get friend object if current user invite user 
     * @param   User    $user   User object whose is or not invitee by current one
     * @return  Collection|Friend
     */
    public function getInvitedByFriend(User $user): Collection
    {
        $criteria = FriendRepository::createNotRejectedFriendsByInviterCriteria($user);

        return $this->invitedByFriends->matching($criteria);
    }

}
