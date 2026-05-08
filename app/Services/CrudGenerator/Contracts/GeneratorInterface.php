<?php

namespace App\Services\CrudGenerator\Contracts;

interface GeneratorInterface
{
    public function generate(array $config): string;
}
