<?php

namespace Sharpen\Versionna\Storage\Database\Adapters\Schema;

interface CreateSchema
{
    public function createTable(): void;
}
