<?php

namespace App\EventListener\Doctrine;

use App\Attribute\TranslationEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class TranslatableListener implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $metadata = $args->getClassMetadata();
        $reflClass = $metadata->getReflectionClass();

        if (!$reflClass) {
            return;
        }

        $attributes = $reflClass->getAttributes(TranslationEntity::class);

        if (empty($attributes)) {
            return;
        }

        if ($metadata->hasAssociation('translations')) {
            return;
        }

        $reflProperty = $this->findPropertyInClassOrTraits($reflClass, 'translations');

        if (!$reflProperty) {
            throw new \LogicException(sprintf(
                'Class "%s" uses #[TranslationEntity] but has no $translations property. Did you forget TranslatableTrait?',
                $reflClass->getName()
            ));
        }

        $attribute = $attributes[0]->newInstance();

        $metadata->mapOneToMany([
            'fieldName'     => 'translations',
            'targetEntity'  => $attribute->class,
            'mappedBy'      => 'translatable',
            'cascade'       => ['persist', 'remove'],
            'orphanRemoval' => true,
            'indexBy'       => 'locale',
        ]);

        // ✅ On utilise $reflProperty déjà trouvé, pas $reflClass->getProperty()
        $reflProperty->setAccessible(true);
        $metadata->reflFields['translations'] = $reflProperty;
    }

    private function findPropertyInClassOrTraits(\ReflectionClass $reflClass, string $property): ?\ReflectionProperty
    {
        // Cherche d'abord directement sur la classe
        if ($reflClass->hasProperty($property)) {
            return $reflClass->getProperty($property);
        }

        // Cherche ensuite dans les traits utilisés
        foreach ($reflClass->getTraits() as $trait) {
            if ($trait->hasProperty($property)) {
                return $trait->getProperty($property);
            }

            // Cherche récursivement dans les traits des traits
            $found = $this->findPropertyInClassOrTraits($trait, $property);
            if ($found) {
                return $found;
            }
        }

        return null;
    }
}