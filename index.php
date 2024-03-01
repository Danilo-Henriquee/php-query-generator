<?php
include('./db.php');
include('./entitys.php');
include('./entityServiceLayer.php');

interface CRUD {
    public function save(string $obj) : void;
    public function saveAll(array $obj) : void;
    public function findAll();
    public function findById(int $id);
    public function deleteAll(int $id);
    public function deletebyId(int $id);
}

interface Entity {
    public static function relationships() : array;
}

class Repository extends EntityServiceLayer implements CRUD {

    private string $className;
    private string $tableName;

    public function __construct(string $className) {
        try {
            if (class_exists($className)) {
                if (in_array('Entity', class_implements($className))) {
                    $reflection = new ReflectionClass($className);

                    $this->className = $className;
                    $this->tableName = $reflection->getConstant('table');
                }
                else throw new Exception("The given class does not implement Entity interface.");
            }
            else throw new Exception("The given class does not exists.");
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
        catch (Error) {
            throw new Exception("table constant in $className class has not defined.");
        }
    }

    public function findAll() : array {
        $columns = Parent::getColumns($this->className, true);
        $tableName = $this->tableName;

        $query = "SELECT $columns FROM $tableName";

        $queryResult = mysqli_query(Parent::getConnection(), $query);

        $results = [];
        while ($row = $queryResult->fetch_assoc()) {
            if (Parent::hasRelationship($this->className)) {
                $childResult = Parent::getRelationships($this->className, $row['id']);
                $row[$childResult[0]] = $childResult[1];

                array_push($results, $row);
            }
        }
        echo(json_encode($results));

        return Parent::formatNumbers($results);
    }

    public function findById(int $id) : array {
        $columns = Parent::getColumns($this->className, true);
        $tableName = $this->tableName;

        $query = "SELECT $columns FROM $tableName WHERE id = ?";

        $statement = mysqli_prepare(Parent::getConnection(), $query);

        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        if (Parent::hasRelationship($this->className)) {
            $childResult = Parent::getRelationships($this->className, $result['id']);
            $result[$childResult[0]] = $childResult[1];
        }

        return $result;
    }

    public function save(string $json) : void {
        $values       = json_decode($json, true);

        $tableName    = $this->tableName;
        $columns      = Parent::getColumns($this->className, false);
        $placeHolders = rtrim(str_repeat('?,', Parent::getColumnsLength($this->className)), ', ');

        $query = "INSERT INTO $tableName ($columns) VALUES ($placeHolders);";

        $statement = mysqli_prepare(Parent::getConnection(), $query);
        $statement->execute(array_values($values));

        $result = $statement->get_result()->fetch_assoc();
    }

    public function saveAll(array $arr) : void {
        $tableName    = $this->tableName;
        $columns      = Parent::getColumns($this->className, false);
        $placeHolder  = rtrim(str_repeat('?,', Parent::getColumnsLength($this->className)), ', ');
        $placeHolders = rtrim(str_repeat("($placeHolder),", count($arr)), ', ');

        $query = "INSERT INTO $tableName ($columns) VALUES $placeHolders;";

        $statement = mysqli_prepare(Parent::getConnection(), $query);
    
        $types = Parent::getTypes($this->className, count($arr));

        $array = [];
        foreach ($arr as $json) {
            $value = json_decode($json, true);
            $values = array_values($value);
            array_push($array, ...$values);
        }

        $statement->bind_param($types, ...$array);
        $statement->execute();
    }

    public function deleteAll(int $id = -1) {
        if ($id == -1) {

        }
    }

    public function deletebyId(int $id) {
    }
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
$humanRepository->save($humanJsonString);
$humanRepository->saveAll($humanJsonArray);
$animalRepository->saveAll($animalJsonArray); */

$humanRepository->findAll();

