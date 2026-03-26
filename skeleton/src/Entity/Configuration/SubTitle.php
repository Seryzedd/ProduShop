<?php

namespace App\Entity\Configuration;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Interface\HtmlTextInterface;

#[ORM\Entity()]
class SubTitle extends AbstractText implements HtmlTextInterface
{
    public function __construct()
    {
        $this->setTag('h2');
    }
}