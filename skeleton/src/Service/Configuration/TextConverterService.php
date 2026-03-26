<?php

namespace App\Service\Configuration;

use App\Entity\Configuration\AbstractText;
use Doctrine\ORM\EntityManagerInterface;

class TextConverterService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function convertTo(AbstractText $source, string $targetClass): AbstractText
    {
        // Vérifie que la classe cible est bien une sous-classe d'AbstractText
        if (!is_subclass_of($targetClass, AbstractText::class)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" n\'est pas une sous-classe de AbstractText.', $targetClass)
            );
        }

        // Même type : rien à faire
        if ($source instanceof $targetClass) {
            return $source;
        }

        // Crée la nouvelle entité du bon type en copiant les données communes
        /** @var AbstractText $new */
        $new = new $targetClass();
        $new->setContent($source->getContent());
        $new->setColor($source->getColor());
        $new->setAlign($source->getAlign());
        $new->setClasses($source->getClasses());
        $new->setBlock($source->getBlock());

        // Supprime l'ancienne entité, persiste la nouvelle
        $this->em->remove($source);
        $this->em->persist($new);
        $this->em->flush();

        return $new;
    }
}