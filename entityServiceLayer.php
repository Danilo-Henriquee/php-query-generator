<?php

class EntityServiceLayer extends Db {
    public static function getRelationships(string $className, int $id) {
        $relationships = $className::relationships();
            foreach ($relationships as $relation):
                $jsonIgnore = $relation[5];

                if (!$jsonIgnore) {
                    list($type, $referencedClass, $mappedBy, $referencedForeignColumn) = $relation;
                    
                    $reflectClass = new ReflectionClass($referencedClass);
                    
                    $columns = self::getColumns($referencedClass, true);
                    $table = $reflectClass->getConstant('table');
                        
                    $query = "SELECT $columns FROM $table WHERE $referencedForeignColumn = ?";

                    $statement = mysqli_prepare(Db::getConnection(), $query);
                    $statement->bind_param('i', $id);
                    $statement->execute();                    
    
                    $result = $statement->get_result();
                    $results = [];

                    while ($row = $result->fetch_assoc()) {
                        $results[] = $row;
                    }

                    switch ($type) {
                        case 'OneToMany':
                            if ($referencedClass::relationships() != []) {
                                for ($i = 0; $i < count($results); $i++) {
                                    $result = $results[$i];
        
                                    $childId = $result['id'];
                                    $childResult = self::getRelationships($referencedClass, $childId);

                                    if (!empty($childResult[1])) {
                                        $values = self::formatNumbers($childResult[1]);

                                        $results[$i][$childResult[0]] = $values;
                                        continue;
                                    };
                                    $results[$i][$childResult[0]] = [];
                                }
                            }
                            return [$mappedBy, $results];

                        case 'ManyToOne':

                    }
                }
            endforeach;

    }

    public static function formatNumbers(array $arrays) : array {
        foreach ($arrays as $array) {
            $keys = array_keys($array);
            $values = array_values($array);

            $values = array_map(function ($val) {
                if (is_numeric($val)) {
                    if (strpos($val, '.')) return (float) $val;
                    else return (int) $val;
                }
                return $val;
            }, $values);
        }   

        return array_combine($keys, $values);
    }

    public static function hasRelationship(string $className) : bool {
        $relationships = $className::relationships();

        if (!empty($relationships)) return true;
        return false;
    }

    public static function getColumnsLength(string $className) : int {
        $reflection = new ReflectionClass($className);
        $props = $reflection->getProperties();

        return count($props) - 1;
    }

    public static function getColumns(string $className, bool $includeId) : string {
        $reflection = new ReflectionClass($className);
        $props = $reflection->getProperties();

        $filteredProps = [];
        foreach ($props as $prop) {
            if ($prop->name == 'id' && !$includeId) continue;
            $filteredProps[] = $prop->name;
        }

        return implode(', ', $filteredProps);
    }

    public static function getTypes(string $className, int $times) : string {
        $reflection = new ReflectionClass($className);
        $props = $reflection->getProperties();

        $types = '';
        foreach ($props as $prop) {
            if ($prop->name !== 'id') {
                $types .= $prop->getType()->getName()[0];
            }
        }

        return str_repeat($types, $times);
    }
}