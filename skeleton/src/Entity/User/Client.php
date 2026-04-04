<?php

namespace App\Entity\User;

use App\Entity\User\Payment\Payment;
use App\Entity\User\Payment\StripeCustomer;
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

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?StripeCustomer $stripe = null;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'customer', orphanRemoval: true)]
    private Collection $payments;

    public function __construct()
    {
        $this->addRole('ROLE_USER');
        $this->shippingAdresses = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getGender():string
    {
        return $this->gender;
    }

    public function getCleanGender(): string
    {
        return array_search($this->gender, $this::__GENDER);
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

    public function getAdressById(int $id)
    {
        $filter = $this->shippingAdresses->filter(static fn (Adress $adress) => $adress->getId() === $id);

        return $filter->first();
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

    public function getStripe(): ?StripeCustomer
    {
        return $this->stripe;
    }

    public function setStripe(?StripeCustomer $stripe): static
    {
        // unset the owning side of the relation if necessary
        if ($stripe === null && $this->stripe !== null) {
            $this->stripe->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($stripe !== null && $stripe->getUser() !== $this) {
            $stripe->setUser($this);
        }

        $this->stripe = $stripe;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setCustomer($this);
        }

        return $this;
    }

    public function getPaymentsByDate()
    {
        $iterator = $this->payments->getIterator();

        $iterator->uasort(function ($first, $second) {
            if ($first === $second) {
                return 0;
            }

            return (float) $first->getCreatedAt()->format("U.u") < (float) $second->getCreatedAt()->format("U.u") ? -1 : 1;
        });

        return $iterator;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getCustomer() === $this) {
                $payment->setCustomer(null);
            }
        }

        return $this;
    }

    public static function getAvailableRoles(): array
    {
        $roles = parent::getAvailableRoles();

        unset($roles['ROLE_SELLER']);

        return $roles;
    }
}