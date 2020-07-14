<?php declare(strict_types=1);

namespace App\Model\User;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\ContainsAlphanumeric;

class RenewPasswordModel
{
    
    /**
     * @Assert\NotBlank(message="Please enter password")
     * @ContainsAlphanumeric()
     */
    private $plainPassword;

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

}
