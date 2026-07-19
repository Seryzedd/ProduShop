<?php

namespace App\Service\Sql;

use App\Entity\Utils\SqlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EntityBuilder\EntityMetaDatas;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use App\Service\String\Normalizer;

class SqlRequestGenerator
{
    private array $alias = [];

    private array $tableNames = []; 

    public function __construct(private EntityManagerInterface $entityManager, private EntityMetaDatas $metadatas, private Normalizer $normalizer) {}
    
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
        $this->addTableDatas($queryClass);

        $way = explode('\\', $queryClass);
        $classname =  end($way);

        $sql = $this->select($sqlconfig->getSelector(), $queryClass);

        $sql .= $this->from($queryClass);

        $sql .= $this->addWhere($sqlconfig->getConditions());

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

            $tableNames = $this->getTableDatas($this->config->getClassNamespace($where->getAlias()));

            $alias = $this->getAliasValue($where->getAlias());
            $fieldName = $tableNames->getColumnName($where->getField());

            $conditions .= $alias . '.' . $fieldName . ' ' . $where->getOperator() . ' ' . $this->insertValue($where->getOperator(), $where->getValue()) . ' ';
        }

        return $conditions;
    }

    private function insertValue(string $operator, string $value): string
    {
        $string = '';

        switch($operator) {
            case '=':
            case '>':
            case '<':
            case '<=':
            case '>=':
            case '!=':
                $string = '\'' . $value . '\'';
                break;
            case 'LIKE':
                $string = '\'%' . $value . '%\'';
                break;
            case 'IN':
                $string = $this->toListStr($this->normalizer->stringToList($value));
                break;
        }

        return $string;
    }

    private function toListStr(array $list): string
    {
        $response = '(';

        foreach($list as $key => $val) {
            $response .= '\'' . $val . '\'';

            if(count($list) !== $key + 1) {
                $response .= ',';
            }
        }

        $response .= ')';

        return $response;
    }

    private function addAlias(string $alias, string $name): array
    {
        $this->alias[$alias] = $name;

        return $this->alias;
    }

    private function getAliases(): array
    {
        return $this->alias;
    }

    private function getAliasValue(string $alias): string
    {
        return $this->alias[$alias];
    }

    private function getTablesDatas(): array
    {
        return $this->tableNames;
    }

    private function addTableDatas(string $name): void
    {
        $tableNames = $this->metadatas->getMetadatas($name);

        $this->tableNames[$name] = $tableNames;
    }

    private function getTableDatas(string $name): ClassMetadata
    {
        return $this->tableNames[$name];
    }
}