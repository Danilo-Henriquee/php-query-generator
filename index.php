<?php

use function PHPSTORM_META\type;
include('./db.php');


interface CRUD {
    public function save(string $obj);
    public function saveAll(array $obj);
    public function findAll();
    public function findById(int $id);
    public function deleteAll(int $id);
    public function deletebyId(int $id);
}

class Repository implements CRUD {

    private string $className;
    private ?string $tableName;
    private array $properties;
    private Db $db;

    const SELECT = "SELECT";
    const INSERT = "INSERT";
    const UPDATE = "UPDATE";
    const DELETE = "DELETE";

    public function __construct(string $className, array $args = []) {
        if (class_exists($className) && is_subclass_of($className, 'Repository')) {
            $this->db = new Db();

            $this->className = $className;
            $this->tableName = null;

            $reflection = new ReflectionClass($className);

            $reflectionProperties = $reflection->getProperties();
            
            $properties = array_map(function ($prop) {
                return [$prop->name, $prop->getType()->getName()];
            }, $reflectionProperties);

            $this->properties = $properties;

            if ($args['tableName']) $this->tableName = $args['tableName'];
        }
        else throw new Exception("The given class doesn't managed by repository");
    }

    public function getPropertiesLength() : int {
        return count($this->properties) - 1;
    }

    public function getTypes(int $times) : string {
        $values = array_map(
            function ($value) {
                return $value[1][0];
            }, 
            array_filter($this->properties, 
                function ($val) {
                    return $val[0] !== 'id';
                })
        );

        return str_repeat(implode('', $values), $times);
    }

    public function getProperties() : string {
        $properties = array_filter(
            array_map(function ($value) {
                return $value[0];
            }, $this->properties),
            function ($val) {
                return $val != 'id';
            }
        );

        return implode(', ', $properties);
    }

    public function findAll() {
        
    }

    public function findById(int $id) {

    }

    public function save(string $json) : void {
        $values = json_decode($json, true);

        $tableName    = strtolower($this->tableName ? $this->tableName : $this->className);
        $columns      = $this->getProperties();
        $placeHolders = rtrim(str_repeat('?,', $this->getPropertiesLength()), ', ');

        $query = "INSERT INTO $tableName ($columns) VALUES ($placeHolders);";

        echo $query;

        $statement = mysqli_prepare($this->db->getConnection(), $query);
        $statement->execute(array_values($values));
    }

    public function saveAll(array $arr) : void {
        $tableName    = strtolower($this->tableName ? $this->tableName : $this->className);
        $columns      = $this->getProperties();
        $placeHolder  = rtrim(str_repeat('?,', $this->getPropertiesLength()), ', ');
        $placeHolders = rtrim(str_repeat("($placeHolder),", count($arr)), ', ');

        $query = "INSERT INTO $tableName ($columns) VALUES $placeHolders;";
        echo $query;

        $statement = mysqli_prepare($this->db->getConnection(), $query);
    
        $types = $this->getTypes(count($arr));

        $array = [];
        foreach ($arr as $json) {
            $value = json_decode($json, true);
            $values = array_values($value);
            array_push($array, ...$values);
        }

        $statement->bind_param($types, ...$array);
        $statement->execute();
    }

    public function deleteAll(int $id) {

    }

    public function deletebyId(int $id) {

    }
}

class Human extends Repository {

    private string $id;
    private string $name;
    private string $lastName;
    private string $age;

    public function __construct() {
        
    }
}

class Animal extends Repository {

    private string $id;
    private string $name;
    private string $race;
    private string $color;
    private string $owner;
}

$humanRepository = new Repository('Human');
$animalRepository = new Repository('Animal');

/* $animalRepository->showProperties(); */

$humanJsonString = '
    {
        "name": "Danilo",
        "lastName": "Marques",
        "age": 17
    }
';

$humanJsonArray = [
    '{
        "name": "Danilo",
        "lastName": "Marques",
        "age": 17
    }',
    '{
        "name": "Renato",
        "lastName": "pilula",
        "age": 91
    }',
    '{
        "name": "Paulo",
        "lastName": "Henrique",
        "age": 56
    }',
    '{
        "name": "Bisnagua",
        "lastName": "Margarina",
        "age": 24
    }'
];

$animalJsonString = '
    {
        "name": "colt",
        "race": "Doberman",
        "color": "yellow",
        "owner": "Danilo"
    }
';

$animalJsonArray = [
    '{
        "name": "colt",
        "race": "Doberman",
        "color": "yellow",
        "owner": "Danilo"
    }',
    '{
        "name": "thomas",
        "race": "Rotweiler",
        "color": "orange",
        "owner": "Rogerio"
    }',
    '{
        "name": "robeert",
        "race": "Pintcher",
        "color": "white",
        "owner": "Alemão"
    }'
];

/* $animalRepository->save($animalJsonString);
$humanRepository->save($humanJsonString); */
$humanRepository->saveAll($humanJsonArray);
$animalRepository->saveAll($animalJsonArray);
