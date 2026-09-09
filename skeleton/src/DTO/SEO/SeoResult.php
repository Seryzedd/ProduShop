<?php
// src/Seo/SeoRuleResult.php
namespace App\DTO\SEO;

final class SeoResult
{
    private string $name = '';

    private bool $passed = false;

    private string $message = '';

    private int $weight = 1;

    public function __construct(string $name, string $message, bool $passed = false, int $weight = 1)
    {
        $this->setName($name);
        $this->setPassed($passed);
        $this->setMessage($message);
        $this->setWeight($weight);
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): self
    {
        $this->passed = $passed;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }
}