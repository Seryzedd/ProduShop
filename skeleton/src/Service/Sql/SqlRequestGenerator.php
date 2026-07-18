<?php

namespace App\Service\Sql;

use App\Entity\Utils\SqlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EntityBuilder\EntityMetaDatas;
use Doctrine\ORM\PersistentCollection;

class SqlRequestGenerator
{
    const  ALIAS = [];

    public function __construct(private EntityManagerInterface $entityManager, private EntityMetaDatas $metadatas) {}
    
    public function getDatas(SqlGenerator $sqlconfig)
    {
        $this->config = $sqlconfig;

        $sql = $this->getSqlQuery($sqlconfig);

        $conn = $this->entityManager->getConnection();

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }

    private function getSqlQuery(SqlGenerator $sqlconfig): string
    {
        $queryClass = $sqlconfig->getClassNamespace($sqlconfig->getEntityclassName());

        $tablename = $this->metadatas->getTableName($queryClass);

        $this->addAlias($sqlconfig->getEntityclassName(), $tablename);

        $way = explode('\\', $queryClass);
        $classname =  end($way);

        $sql = $this->select($sqlconfig->getSelector(), $queryClass);

        $sql .= $this->from($queryClass);

        $sql .= $this->addWhere($sqlconfig->getConditions());

        dump($sql);

        return $sql;
    }

    private function from(string $class): string
    {
        $tablename = $this->metadatas->getTableName($class);

        return ' FROM ' . $tablename . ' ' . $tablename;
    }

    private function select(PersistentCollection $selects, string $from): string
    {
        $sql = '';
        $tableNames = $this->metadatas->getMetadatas($from);
        

        foreach($selects as $selection) {
            $sql .= 'SELECT ';
            $properties = $selection->getProperty();

            foreach($properties as $property) {
                $table = $tableNames->getColumnName($property);

                $alias = $this->getAliasValue($this->config->getEntityClassName());
                
                $sql .= $alias .'.' . $table ;

                if($property !== end($properties)) {
                    $sql .= ', ';
                }
            }
        }
        return $sql;
    }

    private function addWhere(PersistentCollection $wheres): string
    {
        $conditions = ' ';

        foreach($wheres as $where) {
            if($where === $wheres->first()) {
                $conditions .= 'WHERE ';
            } else {
                $conditions .= 'ANDWHERE ';
            }

            $alias = $this->getAliasValue($where->getAlias());
            dump();

            $conditions .= $alias . '.' . $where->getField() . ' ' . $where->getOperator() . ' \'' . $where->getValue() . '\' ';
        }

        return $conditions;
    }

    private function addAlias(string $alias, string $name): array
    {
        $this->ALIAS[$alias] = $name;

        return $this->ALIAS;
    }

    private function getAliases(): array
    {
        return $this->ALIAS;
    }

    private function getAliasValue(string $alias): string
    {
        return $this->ALIAS[$alias];
    }
}