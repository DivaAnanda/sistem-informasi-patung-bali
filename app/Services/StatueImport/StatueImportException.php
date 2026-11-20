<?php

namespace App\Services\StatueImport;

use RuntimeException;

class StatueImportException extends RuntimeException
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(count($errors).' masalah ditemukan pada file CSV.');
    }
}
