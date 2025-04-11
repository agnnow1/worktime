<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\WorkTimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[AllowDynamicProperties] #[ORM\Entity(repositoryClass: WorkTimeRepository::class)]
class WorkTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Employee $employee;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $startDateTime;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $endDateTime;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $workDay;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getStartDateTime(): \DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): \DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTimeInterface $endDateTime): static
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    public function getWorkDay(): \DateTimeInterface
    {
        return $this->workDay;
    }

    public function setWorkDay(\DateTimeInterface $workDay): static
    {
        $this->workDay = $workDay;

        return $this;
    }
}
