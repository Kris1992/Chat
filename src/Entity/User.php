<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Services\ImagesManager\ImagesConstants;
use App\Repository\UserRepository;
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
     * @Groups({"chat:message"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Groups({"chat:message", "chat:participants"})
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
     * @Groups({"chat:message", "chat:participants"})
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
     * @Groups({"chat:message", "chat:participants"})
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

    public function addInvitedFriend(Friend $invitedFriend): self
    {
        if (!$this->invitedFriends->contains($invitedFriend)) {
            $this->invitedFriends[] = $invitedFriend;
            $invitedFriend->setInviter($this);
        }

        return $this;
    }

    public function removeInvitedFriend(Friend $invitedFriend): self
    {
        if ($this->invitedFriends->contains($invitedFriend)) {
            $this->invitedFriends->removeElement($invitedFriend);
            // set the owning side to null (unless already changed)
            if ($invitedFriend->getInviter() === $this) {
                $invitedFriend->setInviter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friend[]
     */
    public function getInvitedByFriends(): Collection
    {
        return $this->invitedByFriends;
    }

    public function addInvitedByFriend(Friend $invitedByFriend): self
    {
        if (!$this->invitedByFriends->contains($invitedByFriend)) {
            $this->invitedByFriends[] = $invitedByFriend;
            $invitedByFriend->setInvitee($this);
        }

        return $this;
    }

    public function removeInvitedByFriend(Friend $invitedByFriend): self
    {
        if ($this->invitedByFriends->contains($invitedByFriend)) {
            $this->invitedByFriends->removeElement($invitedByFriend);
            // set the owning side to null (unless already changed)
            if ($invitedByFriend->getInvitee() === $this) {
                $invitedByFriend->setInvitee(null);
            }
        }

        return $this;
    }

}
