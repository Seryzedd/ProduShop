<?php

namespace App\Entity\Configuration;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Configuration\AbstractText;
use App\Interface\HtmlTextInterface;

#[ORM\Entity()]
class Link extends AbstractText implements HtmlTextInterface
{

    #[ORM\Column]
    private string $target = '';

    public function __construct()
    {
        $this->setTag('a');
    }

    public function getHtml(): string
    {
        // ✅ Utilisation des getters car les propriétés sont private dans le parent
        return sprintf(
            '<%s class="%s" target="%s" style="color: %s; text-align: %s;">%s</%s>',
            $this->getTag(),
            $this->getStringClasses(),
            $this->getTarget(),
            $this->getColor(),
            $this->getAlign(),
            $this->getContent(),
            $this->getTag()
        );
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }
}