<?php declare(strict_types=1);

namespace SiroDiaz\ManticoreMigration\Storage;

use PDO;
use PDOException;

class DatabaseConnection
{
	/**
	 * @var PDO
	 */
    private $connection;

	/**
	 * @var DatabaseConfiguration
	 */
    private $configuration;

    public function __construct(DatabaseConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->build();
    }

	protected function createConnectionByType()
	{
		switch ($this->configuration->getDriver()) {
			case 'sqlite':
				$this->connection = $this->createSQLiteConnection();
				break;
			default:
				$this->connection = $this->createConnection();
		}
	}

	protected function createSQLiteConnection()
	{
		return new PDO(
			"sqlite:{$this->configuration->getDatabase()}",
			null,
			null,
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);
	}

	protected function createConnection()
	{
		return new PDO(
			$this->configuration->getDsn(),
			$this->configuration->getUser(),
			$this->configuration->getPassword(),
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);
	}

	/**
     * @throws PDOException
     * @return void
     */
    private function build(): void
    {
		$this->createConnectionByType();
	}

	/**
	 * @return DatabaseConfiguration
	 */
	public function getConfiguration(): DatabaseConfiguration
	{
		return $this->configuration;
	}

	/**
	 * @return PDO
	 */
	public function getConnection(): PDO
	{
		return $this->connection;
	}
}
