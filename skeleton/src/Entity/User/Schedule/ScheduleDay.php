<?php

namespace App\Entity\User\Schedule;

use App\Entity\User\OpeningSchedule;
use App\Repository\User\Schedule\ScheduleDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleDayRepository::class)]
class ScheduleDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $day = null;

    const _DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * @var Collection<int, Hours>
     */
    #[ORM\OneToMany(targetEntity: Hours::class, mappedBy: 'day', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $hours;

    #[ORM\ManyToOne(inversedBy: 'scheduleDays')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OpeningSchedule $openingSchedule = null;

    public function __construct(int $day = 1)
    {
        $this->hours = new ArrayCollection();
        $this->day = $day;

        $this->addHour(new Hours());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getDayName(): string
    {
        return $this::_DAYS[$this->day];
    }

    /**
     * @return Collection<int, Hours>
     */
    public function getHours(): Collection
    {
        return $this->hours;
    }

    public function addHour(Hours $hour): static
    {
        if (!$this->hours->contains($hour)) {
            $this->hours->add($hour);
            $hour->setDay($this);
        }

        return $this;
    }

    public function removeHour(Hours $hour): static
    {
        if ($this->hours->removeElement($hour)) {
            // set the owning side to null (unless already changed)
            if ($hour->getDay() === $this) {
                $hour->setDay(null);
            }
        }

        return $this;
    }

    public function getOpeningSchedule(): ?OpeningSchedule
    {
        return $this->openingSchedule;
    }

    public function setOpeningSchedule(?OpeningSchedule $openingSchedule): static
    {
        $this->openingSchedule = $openingSchedule;

        return $this;
    }
}
