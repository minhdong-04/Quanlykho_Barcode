<?php

namespace App\Services;

interface RepositoryInterface
{
    public function all(array $filters = []);
}
