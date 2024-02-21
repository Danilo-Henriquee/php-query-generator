<?php

class Db {

    const host = "localhost";
    const username = "root";
    const password = "";
    const database = "teste";

    private $connection;

    public function __construct() {
        $this->connection = mysqli_connect(Db::host, Db::username, Db::password, Db::database);
    }

    public function query($sql) {
        if (!$this->connection) {
            throw new Exception(mysqli_connect_error());
        }

        mysqli_query($this->connection, $sql);
    }

    public function getConnection() {
        return $this->connection;
    }
}