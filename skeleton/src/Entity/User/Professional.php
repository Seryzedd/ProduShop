<?php

namespace App\Entity\User;

use App\Entity\Picture;
use App\Entity\Product\Product;
use App\Entity\User\Payment\StripeMerchant;
use App\Entity\User\PostalAdress\Adress;
use App\Repository\User\ProfessionalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\AbstractUser;
use App\Entity\User\Schedule\Hours;

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

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $products;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Picture $logo = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?StripeMerchant $stripeAccount = null;

    #[ORM\OneToOne(mappedBy: 'User', cascade: ['persist', 'remove'])]
    private ?OpeningSchedule $openingSchedule = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'merchant')]
    private Collection $orders;

    public function __construct()
    {
        $this->addRole('ROLE_SELLER');
        $this->adress = new Adress();
        $this->products = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCompany($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCompany() === $this) {
                $product->setCompany(null);
            }
        }

        return $this;
    }

    public function getLogo(): ?Picture
    {
        return $this->logo;
    }

    public function setLogo(?Picture $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStripeAccount(): ?StripeMerchant
    {
        return $this->stripeAccount;
    }

    public function setStripeAccount(?StripeMerchant $stripeAccount): static
    {
        // unset the owning side of the relation if necessary
        if ($stripeAccount === null && $this->stripeAccount !== null) {
            $this->stripeAccount->setOneToOne(null);
        }

        // set the owning side of the relation if necessary
        if ($stripeAccount !== null && $stripeAccount->getOneToOne() !== $this) {
            $stripeAccount->setOneToOne($this);
        }

        $this->stripeAccount = $stripeAccount;

        return $this;
    }

    public function getOpeningSchedule(): ?openingSchedule
    {
        return $this->openingSchedule;
    }

    public function setOpeningSchedule(?openingSchedule $openingSchedule): static
    {
        // unset the owning side of the relation if necessary
        if ($openingSchedule === null && $this->openingSchedule !== null) {
            $this->openingSchedule->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($openingSchedule !== null && $openingSchedule->getUser() !== $this) {
            $openingSchedule->setUser($this);
        }

        $this->openingSchedule = $openingSchedule;

        return $this;
    }

    public function isOpen()
    {
        $open = false;

        $schedule = $this->getOpeningSchedule();

        if(!$schedule) {
            return $open;
        }

        $currentDay = $schedule->getToday();

        $today = new \Datetime();

        $now = ($today->format('G') * 60) + $today->format('i');

        $filtered = $currentDay->getHours()->filter(static fn (Hours $hours) => $hours->getStartNumber() < $now && $hours->getEndNumber() > $now);

        if(count($filtered) > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setMerchant($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getMerchant() === $this) {
                $orderItem->setMerchant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setMerchant($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getMerchant() === $this) {
                $order->setMerchant(null);
            }
        }

        return $this;
    }
}
