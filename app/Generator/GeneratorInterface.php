<?php
namespace App\Generator;

Interface GeneratorInterface {
    public function parse () ;
    public function create(): void;
}
