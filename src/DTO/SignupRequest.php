<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

class SignupRequest
{
    #[Assert\NotBlank]
    public string $email;
    #[Assert\NotBlank]
    public string $password;
    #[Assert\NotBlank]
    public string $nameOrg;
    #[Assert\NotBlank]
    public string $phone;
    public \DateTimeInterface $createdAt;
    public \DateTimeInterface $updatedAt;
    public \DateTimeInterface $dateActiveTo;

    public function __construct(DateTime $dateNow = new \DateTime())
    {
        $this->createdAt = $dateNow;
        $this->updatedAt = $dateNow;
        $this->dateActiveTo = $dateNow;
    }

}