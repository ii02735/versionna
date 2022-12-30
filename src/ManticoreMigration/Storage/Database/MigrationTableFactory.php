<?php

namespace SiroDiaz\ManticoreMigration\Storage\Database;

use SiroDiaz\ManticoreMigration\Storage\Database\Adapters\Schema;
use SiroDiaz\ManticoreMigration\Storage\DatabaseConnection;

class MigrationTableFactory
{
	/**
	 * @var DatabaseConnection
	 */
	protected DatabaseConnection $connection;

	protected string $tableName;

	public function __construct(DatabaseConnection $connection, string $tableName)
	{
		$this->connection = $connection;
		$this->tableName = $tableName;
	}

	public function getDriver(): string
	{
		return $this->connection->getConfiguration()->getDriver();
	}

	public function make(): Schema
	{
		$namespace = 'SiroDiaz\ManticoreMigration\Storage\Database\Adapters';
		$className = ucfirst($this->getDriver());

		$adapterClassName = "{$namespace}\\{$className}";

		return new $adapterClassName($this->connection->getConnection(), $this->tableName);
	}
}
