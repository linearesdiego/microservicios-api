<?php

class Persona
{
    static private $siguiente_id = 1;
    public $id = 0;
    public $nombre;
    public $edad;

    public function __construct($nombre, $edad)
    {
        $this->id = self::$siguiente_id++;
        $this->nombre = $nombre;
        $this->edad = $edad;
    }

    public static function obtener_siguiente_id()
    {
        return self::$siguiente_id;
    }

    public function saludar()
    {
        return "Hola, mi nombre es {$this->nombre}, tengo {$this->edad} años y mi identificador es {$this->id}";
    }
}

$personas = [];
$personas[] = new Persona("Juan", 30);
$personas[] = new Persona("María", 25);
$personas[] = new Persona("Francisco", 40);

$personas[0]->edad = 31; // Modificar la edad de Juan

foreach ($personas as $persona) {
    echo $persona->saludar() . "\n";
}

for ($i = 0; $i < count($personas); $i++) {
    $persona = $personas[$i];
    echo $persona->saludar() . "\n";
}

echo "El siguiente identificador será: " . Persona::obtener_siguiente_id() . "\n";
