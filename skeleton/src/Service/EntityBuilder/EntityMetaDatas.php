<?php

namespace App\Service\EntityBuilder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityMetaDatas
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Retourne un tableau [nomChamp => 0] pour tous les champs scalaires d'une entité
     */
    public function buildDefaults(string $entityClass): array
    {
        $metadata = $this->em->getClassMetadata($entityClass);
        $defaults = [];

        foreach ($metadata->getFieldNames() as $field) {
            $defaults[ucfirst($field)] = $field;
        }

        return $defaults;
    }

    public function getTableName(string $namespace)
    {
        $metadata = $this->em->getClassMetadata($namespace);

        return $metadata->getTableName();
    }

    public function getMetadatas(string $entityClass): ClassMetadata
    {
        return $this->em->getClassMetadata($entityClass);
    }
}