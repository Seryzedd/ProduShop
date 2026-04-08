<?php

namespace App\Entity\Translations;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Configuration\AbstractText;
use Doctrine\DBAL\Types\Types;
use App\Interface\TranslationInterface;
use App\Trait\TranslationTrait;
use App\Attribute\TranslationEntity;

#[ORM\Entity]
class TextTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\ManyToOne(inversedBy: 'translations', cascade: ['persist', 'remove'])]
    private AbstractText $translatable;

    #[ORM\Column(type: Types::TEXT)]
    protected string $content = '';

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getTranslatable(): AbstractText
    {
        return $this->translatable;
    }

    public function setTranslatable(object $translatable): void
    {
        $this->translatable = $translatable;

        $translatable->addTranslation($this);
    }
}