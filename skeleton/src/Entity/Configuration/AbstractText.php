<?php

namespace App\Entity\Configuration;

use App\Entity\Configuration\Homepage\Block;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Configuration;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'text_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'mainTitle' => Configuration\MainTitle::class,
    'subTitle' => Configuration\SubTitle::class,
    'normalTitle' => Configuration\NormalTitle::class,
    'littleTitle' => Configuration\LittleTitle::class,
    'paragraph' => Configuration\Paragraph::class,
    'link' => Configuration\Link::class
])]
abstract class AbstractText
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column]
    protected string $color = '';

    #[ORM\Column]
    protected string $align = 'start';

    #[ORM\Column]
    protected string $tag = '';

    #[ORM\Column(type: 'json')]
    protected array $classes = [];

    #[ORM\Column(type: Types::TEXT)]
    protected string $content = '';

    #[ORM\ManyToOne(targetEntity: Block::class, inversedBy: 'htmlElement', cascade: ['persist', 'remove'])]
    protected ?Block $block = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function setAlign(string $alignement): static
    {
        $this->align = $alignement;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function setClasses(array $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function addClass(string $class): static
    {
        if(!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }

        return $this;
    }

    public function removeClass(string $class): static
    {
        if(in_array($class, $this->classes)) {
            unset($this->classes[array_search($class, $this->classes)]);
        }
        return $this;
    }

    public function getStringClasses(): string
    {
        $classes = '';
        foreach ($this->classes as $class) {
            $class .= $class . ' ';
        }

        return $classes;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function setBlock(?Block $block): static
    {
        // unset the owning side of the relation if necessary
        $this->block = $block;

        // set the owning side of the relation if necessary
        if ($block !== null && $block->getHtmlElement() !== $this) {
            $block->addHtmlElement($this);
        }

        $this->block = $block;

        return $this;
    }

    public function getHtml(): string
    {
        
        $html = sprintf(
            '<%s class="%s" style="color: %s; text-align: %s;">%s</%s>',
            $this->getTag(),
            $this->getStringClasses(),
            $this->getColor(),
            $this->getAlign(),
            nl2br($this->getContent()),
            $this->getTag()
        );

        return $html;
    }
}
