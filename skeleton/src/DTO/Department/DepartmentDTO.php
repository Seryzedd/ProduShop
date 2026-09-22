<?php

namespace App\DTO\Department;

class DepartmentDTO
{
    public function __construct(
        private string $code,
        private string $name,
        private string $regionCode,
        private string $regionName,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    public function getRegionName(): string
    {
        return $this->regionName;
    }
}