<?php

namespace App\Entity\User\Schedule;

use App\Repository\User\Schedule\HoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: HoursRepository::class)]
class Hours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $startHour = 8;

    #[ORM\Column]
    private int $startMinutes = 0;

    #[ORM\Column]
    private int $endHour = 9;

    #[ORM\Column]
    private int $endMinutes = 0;

    #[ORM\ManyToOne(inversedBy: 'hours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScheduleDay $day = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartHour(): int
    {
        return $this->startHour;
    }

    public function setStartHour(int $startHour): static
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getStartMinutes(): int
    {
        return $this->startMinutes;
    }

    public function setStartMinutes(int $startMinutes): static
    {
        $this->startMinutes = $startMinutes;

        return $this;
    }

    public function getStartNumber(): int
    {
        return ($this->getStartHour() * 60) + $this->getStartMinutes();
    }

    public function getEndHour(): int
    {
        return $this->endHour;
    }

    public function setEndHour(int $endHour): static
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getEndMinutes(): int
    {
        return $this->endMinutes;
    }

    public function setEndMinutes(int $endMinutes): static
    {
        $this->endMinutes = $endMinutes;

        return $this;
    }

    public function getEndNumber(): int
    {
        return ($this->getEndHour() * 60) + $this->getEndMinutes();
    }

    public function getDay(): ?ScheduleDay
    {
        return $this->day;
    }

    public function setDay(?ScheduleDay $day): static
    {
        $this->day = $day;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $startTotal = ($this->startHour * 60) + $this->startMinutes;
        $endTotal   = ($this->endHour   * 60) + $this->endMinutes;

        if ($startTotal >= $endTotal) {
            $context->buildViolation("L'heure de fin doit être supérieure à l'heure de début.")
                ->atPath('endHour')
                ->addViolation();
        }
    }
}
