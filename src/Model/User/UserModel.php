<?php
declare(strict_types=1);

namespace App\Model\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\{UniqueUser, ContainsAlphanumeric};

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
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\NotBlank(message="Please enter a login")
     */
    private $login;

    /**
     * @Assert\NotBlank(message="Please enter password", groups={"registration"})
     * @ContainsAlphanumeric()
     */
    private $plainPassword;

    private $role;

    /**
     * @Assert\NotNull(message="Please choose a gender")
     */
    private $gender;

    /**
     * @Assert\IsTrue(message="You must agree to our terms")
     */
    private $agreeTerms;

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

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
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

}
