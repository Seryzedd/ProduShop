<?php

namespace App\DTO\Breadcrumb;

class Link
{
    public function __construct(
        private bool $isCurrent = false,
        private ?string $label = '',
        private ?string $route = '/',
        private ?array $routeParameters = [],
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getRouteParameters(): ?array
    {
        return $this->routeParameters;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }
}