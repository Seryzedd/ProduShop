<?php

namespace App\Entity\User;

use App\Entity\User\Schedule\ScheduleDay;
use App\Repository\User\openingScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpeningScheduleRepository::class)]
class OpeningSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'openingSchedule', cascade: ['persist', 'remove'])]
    private ?Professional $User = null;

    /**
     * @var Collection<int, ScheduleDay>
     */
    #[ORM\OneToMany(targetEntity: ScheduleDay::class, mappedBy: 'openingSchedule', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $scheduleDays;

    public function __construct()
    {
        $this->scheduleDays = new ArrayCollection();

        for ($i=0; $i < 7; $i++) { 
            $this->addScheduleDay(new ScheduleDay($i));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Professional
    {
        return $this->User;
    }

    public function setUser(?Professional $User): static
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection<int, ScheduleDay>
     */
    public function getScheduleDays(): Collection
    {
        return $this->scheduleDays;
    }

    public function addScheduleDay(ScheduleDay $scheduleDay): static
    {
        if (!$this->scheduleDays->contains($scheduleDay)) {
            $this->scheduleDays->add($scheduleDay);
            $scheduleDay->setOpeningSchedule($this);
        }

        return $this;
    }

    public function removeScheduleDay(ScheduleDay $scheduleDay): static
    {
        if ($this->scheduleDays->removeElement($scheduleDay)) {
            // set the owning side to null (unless already changed)
            if ($scheduleDay->getOpeningSchedule() === $this) {
                $scheduleDay->setOpeningSchedule(null);
            }
        }

        return $this;
    }

    public function getToday()
    {
        $date = new \Datetime();

        $dayKey = (int) $date->format('N');

        $filtered = $this->scheduleDays->filter(static fn (ScheduleDay $day) => $day->getDay() === ($dayKey - 1));

        return $filtered->first();
    }
}
