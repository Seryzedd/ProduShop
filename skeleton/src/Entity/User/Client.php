<?php

namespace App\Entity\User;

use App\Entity\User\PostalAdress\Adress;
use App\Repository\User\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\AbstractUser;

#[ORM\Entity]
class Client extends AbstractUser
{
    const __GENDER = [
        'Mister' => 'm',
        'Miss' => 'f'
    ];

    #[ORM\Column(length: 10)]
    private string $gender = "";

    #[ORM\Column(length: 255)]
    private string $firstname = '';

    #[ORM\Column(length: 255)]
    private string $lastname = '';

    /**
     * @var Collection<int, Adress>
     */
    #[ORM\OneToMany(targetEntity: Adress::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $shippingAdresses;

    public function __construct()
    {
        $this->addRole('ROLE_USER');
        $this->shippingAdresses = new ArrayCollection();
    }

    public function getGender():string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return Collection<int, Adress>
     */
    public function getShippingAdress(): Collection
    {
        return $this->shippingAdresses;
    }

    public function addShippingAdress(Adress $shippingAdress): static
    {
        if (!$this->shippingAdresses->contains($shippingAdress)) {
            $this->shippingAdresses->add($shippingAdress);
            $shippingAdress->setUser($this);
        }

        return $this;
    }

    public function removeShippingAdress(Adress $shippingAdress): static
    {
        if ($this->shippingAdresses->removeElement($shippingAdress)) {
            // set the owning side to null (unless already changed)
            if ($shippingAdress->getUser() === $this) {
                $shippingAdress->setUser(null);
            }
        }

        return $this;
    }
}
