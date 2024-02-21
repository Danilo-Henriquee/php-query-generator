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

            $reflection->getProperties();
            $properties = [];

            foreach ($reflection->getProperties() as $prop) {
                array_push($properties, $prop->name);
            }
            $this->properties = $properties;

            if ($args['tableName']) $this->tableName = $args['tableName'];
        }
        else throw new Exception("The given class doesn't managed by repository");
    }

    public function showProperties() {
        foreach ($this->properties as $key) {
            echo "<div>$key</div>";
        }
    }

    public function findAll() {

    }

    public function findById(int $id) {

    }

    /* public function save(string $json) {

        $query = "INSERT INTO ";

        if ($this->tableName) $query .= strtolower($this->tableName);
        else $query .= strtolower($this->className);

        $query .= " (";

        $length = count($this->properties);
        for ($i = 1; $i <= $length; $i++) {
            $key = $this->properties[$i - 1];

            if ($key == 'id') continue;

            if ($i == $length) {
                $query .= "$key";
                break;
            }
            $query .= "$key, ";
        }

        $query .= ") VALUES (";

        $arr = json_decode($json, true);
        $length = count($arr);
        $i = 1;
        foreach ($arr as $key => $value) {
            $isString = false;
            if (gettype($value) == 'string') $isString = true;
            if ($i == $length) {
                $query .= $isString ? "'$value'" : "$value";
                break;
            }
            $query .= $isString ? "'$value', " : "$value, ";
            $i++;
        }

        $query .= ");";

        Db::query($query);
    }

    public function saveAll(array $arr) {
        $query = "INSERT INTO ";

        if ($this->tableName) $query .= strtolower($this->tableName);
        else $query .= strtolower($this->className);

        $query .= " (";

        $length = count($this->properties);
        for ($i = 1; $i <= $length; $i++) {
            $key = $this->properties[$i - 1];

            if ($key == 'id') continue;

            if ($i == $length) {
                $query .= "$key";
                break;
            }
            $query .= "$key, ";
        }

        $query .= ") VALUES" . PHP_EOL;
        
        for ($i = 1; $i <= count($arr); $i++) {
            $query .= "(";

            $values = json_decode($arr[$i - 1], true);
            
            $length = count($arr);
            $j = 1;
            foreach ($values as $key => $value) {
                $isString = false;
                if (gettype($value) == 'string') $isString = true;
                if ($j - 1 == $length) {
                    $query .= $isString ? "'$value'" : "$value";
                    break;
                }
                $query .= $isString ? "'$value', " : "$value, ";
                $j++;
            }
            
            if ($i == $length) {
                $query .= ");";
                break;
            }
            $query .= "),";
        }

        Db::query($query);
    } */

    public function save(string $json) {
        $values = json_decode($json, true);

        $tableName    = strtolower($this->tableName ? $this->tableName : $this->className);
        $columns      = implode(', ', $this->properties);
        $placeHolders = rtrim(str_repeat('?,', count($values)), ', ');

        $query = "INSERT INTO $tableName ($columns) VALUES ($placeHolders)";

        $statement = mysqli_prepare($this->db->getConnection(), $query);
        $statement->execute(array_values($values));
    }

    public function saveAll(array $arr) {
        
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

$humanJson = '
    {
        "name": "Danilo",
        "lastName": "Marques",
        "age": 17
    }
';

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

$animalRepository->saveAll($animalJsonArray);
/* $humanRepository->saveAll($animalJsonArray); */
