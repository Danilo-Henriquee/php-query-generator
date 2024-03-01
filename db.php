<?php

class Db {

    const host = "localhost";
    const username = "root";
    const password = "";
    const database = "teste";

    private static $connection;

    public static function getConnection() {
        if (!isset(self::$connection)) {
            self::$connection = new mysqli(Db::host, Db::username, Db::password, Db::database);

            if (self::$connection->connect_error) {
                die("Database connection error" . self::$connection->connect_error);
            }
        }
        return self::$connection;
    }

    /**
     * @param string $referencedClass name of the parent class.
     * @param string $mappedBy (optional) where the result of a determinated register will be inputed.
     * @param string $referencedForeignColumn name of the foreign key in the child table.
     * @param bool $jsonIgnore if false will return all register they will found with the foreign key when the query is maded.
     * 
     */
    public static function oneToMany(string $referencedClass, string $mappedBy, string $referencedForeignColumn, bool $jsonIgnore) : array {
        return [
            'OneToMany',
            $referencedClass,
            $mappedBy,
            $referencedForeignColumn,
            $jsonIgnore
        ];
    }

    public static function ManyToOne(string $referencedClass, string $mappedBy, string $referencedForeignColumn, bool $jsonIgnore) : array {
        return [
            'ManyToOne',
            $referencedClass,
            $mappedBy,
            $referencedForeignColumn,
            $jsonIgnore
        ];
    }
}