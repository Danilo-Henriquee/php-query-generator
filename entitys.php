<?php
class Human implements Entity {
    const table = 'human';

    private string $id;
    private string $name;
    private string $lastName;
    private string $age;

    public static function relationships() : array {
        return [
            Db::oneToMany(Animal::class, 'animals', 'owner', false)
        ];
    }
}

class Animal implements Entity {
    const table = 'animal';

    private string $id;
    private string $name;
    private string $race;
    private string $color;

    public static function relationships() : array {
        return [
            Db::oneToMany(Appointment::class, 'appointments', 'animal', false),
        ];
    }
}

class Appointment implements Entity {
    const table = 'appointment';

    private string $id;
    private string $doctor;
    private string $date;

    public static function relationships() : array {
        return [
            Db::oneToMany(Medication::class, 'medications', 'appointment', false)
        ];
    }
}

class Medication implements Entity {
    const table = 'medication';

    private string $id;
    private string $name;
    private float $measure;
    private int $appointment;

    public static function relationships(): array {
        return [];
    }
}