<?php

namespace App\Entity\User;

use App\Entity\User\PostalAdress\Adress;
use App\Repository\User\ProfessionalRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\AbstractUser;

#[ORM\Entity]
class Professional extends AbstractUser
{
    #[ORM\Column(length: 255)]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Adress $adress;

    public function __construct()
    {
        $this->addRole('ROLE_SELLER');
        $this->adress = new Adress();
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getAdress(): Adress
    {
        return $this->adress;
    }

    public function setAdress(Adress $adress): static
    {
        $this->adress = $adress;

        

        return $this;
    }
}
