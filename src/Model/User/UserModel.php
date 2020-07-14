<?php declare(strict_types=1);

namespace App\Model\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\{UniqueUser, ContainsAlphanumeric};
use App\Services\ImagesManager\ImagesConstants;

/**
* @UniqueUser(
*     field="email",
*     errorPath="email"
*)
* @UniqueUser(
*     field="login",
*     errorPath="login"
*)
*/
class UserModel
{
    
    private $id;

	/**
     * @Assert\NotBlank(message="Please enter an email")
     * @Assert\Length(
     *      max = 180,
     *      maxMessage = "Your email address cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false
     * )
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\NotBlank(message="Please enter a login")
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Your login cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false
     * )
     */
    private $login;

    /**
     * @Assert\NotBlank(message="Please enter password", groups={"registration"})
     * @ContainsAlphanumeric()
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false
     * )
     */
    private $plainPassword;

    /* Always it is atleast one role (ROLE_USER), so we don't need asserts here*/
    private $roles = [];

    /**
     * @Assert\NotNull(message="Please choose a gender")
     */
    private $gender;

    /**
     * @Assert\IsTrue(message="You must agree to our terms")
     */
    private $agreeTerms;

    private $imageFilename;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAgreeTerms(): ?bool
    {
        return $this->agreeTerms;
    }
    
    public function setAgreeTerms(?bool $agreeTerms): self
    {
        $this->agreeTerms = $agreeTerms;

        return $this;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    
    public function getImagePath(): ?string
    {
        return ImagesConstants::USERS_IMAGES.'/'.$this->getLogin().'/'.$this->getImageFilename();
    }

    public function getThumbImagePath(): ?string
    {
        return ImagesConstants::USERS_IMAGES.'/'.$this->getLogin().'/'.ImagesConstants::THUMB_IMAGES.'/'.$this->getImageFilename();
    }

}
