<?php

namespace App\DTO\Department;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DepartmentsCollection
{
    private Collection $departments;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
    }

    public function addDepartment(DepartmentDTO $department): void
    {
        $this->departments->set($department->getCode(), $department);
    }

    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function findByCode(string $code): ?DepartmentDTO
    {
        return $this->departments->get($code);
    }

    public function count(): int
    {
        return $this->departments->count();
    }
}