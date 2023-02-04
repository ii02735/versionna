<?php

declare(strict_types=1);

namespace SiroDiaz\ManticoreMigration\Storage;

use PDO;
use PDOException;
use SiroDiaz\ManticoreMigration\Storage\Database\MigrationTableFactory;

class MigrationTable
{
    /**
     *
     * @var DatabaseConnection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tablePrefix;

    /**
     * @var string
     */
    protected $tableName;

    public const LISTABLE_COLUMNS = ['name', 'version', 'description', 'created_at'];

    public function __construct(
        DatabaseConnection $connection,
        string $tablePrefix,
        string $tableName
    ) {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
        $this->tableName = $tableName;
    }

    /**
     * @return DatabaseConnection
     */
    public function getConnection(): DatabaseConnection
    {
        return $this->connection;
    }

    /**
     * Returns the PDO connection instance
     *
     * @return PDO
     */
    public function getPDOConnection(): PDO
    {
        return $this->connection->getConnection();
    }

    /**
     * Returns the table prefix
     *
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Returns the full table name
     *
     * @return string
     */
    public function getFullTableName(): string
    {
        return "{$this->tablePrefix}{$this->tableName}";
    }

    /**
     *
     * @return bool
     */
    public function exists(): bool
    {
        $migrationTableCreator = new MigrationTableFactory($this->connection, $this->getFullTableName());
        $migrationCreator = $migrationTableCreator->make();

        return $migrationCreator->existsTable();
    }

    public function create(): void
    {
        $migrationTableCreator = new MigrationTableFactory($this->connection, $this->getFullTableName());
        $migrationCreator = $migrationTableCreator->make();

        $migrationCreator->createTable();
        // var_dump($migrationTableCreator->getTableSchema($this->getFullTableName()));
    }

    /**
     * Returns an array with all the migrations that have been executed
     * sorted by descendent version order.
     *
     * @return MigrationEntity[]
     * @throws PDOException
     */
    public function getSortedMigrations(): array
    {
        $query = "SELECT * FROM {$this->getFullTableName()} ORDER BY version DESC, id DESC";

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_CLASS, MigrationEntity::class);

        return empty($result) ? [] : $result;
    }

    public function getLatestVersion()
    {
        $query = "SELECT MAX(version) FROM {$this->getFullTableName()}";

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->execute();

        return $statement->fetchColumn();
    }

    /**
     * Returns the next version ID to be used for the next migration execution
     *
     * @throws PDOException
     * @return int
     */
    public function getNextVersion(): int
    {
        return $this->getLatestVersion() + 1;
    }

    /**
     *
     * @param MigrationEntity $migrationEntity
     * @return bool
     * @throws PDOException
     */
    public function insert(MigrationEntity $migrationEntity): bool
    {
        $query = "INSERT INTO {$this->getFullTableName()} (version, migration_name, description, created_at) VALUES (:version, :migration_name, :description, :created_at)";

        if ($migrationEntity->getCreatedAt() === null) {
            $migrationEntity->generateCreatedAt();
        }
        $statement = $this->getPDOConnection()->prepare($query);
        $statement->bindValue(':version', $migrationEntity->getVersion());
        $statement->bindValue(':migration_name', $migrationEntity->getName());
        $statement->bindValue(':description', $migrationEntity->getDescription());
        $statement->bindValue(':created_at', $migrationEntity->getCreatedAt()->format('Y-m-d h:i:s'));

        return $statement->execute();
    }

    /**
     *
     * @return array|\stdClass[]
     * @throws PDOException
     */
    public function getMigrationsToUndo(): array
    {
        $query = <<<SQL
		SELECT * FROM {$this->getFullTableName()}
		WHERE version = (
			SELECT MAX(version) FROM {$this->getFullTableName()}
		)
		ORDER BY version DESC
		SQL;
        $statement = $this->getPDOConnection()->prepare($query);

        if (! $statement || ! $statement->execute()) {
            return [];
        }

        $latestMigrations = $statement->fetchAll(PDO::FETCH_CLASS);

        return empty($latestMigrations) ? [] : $latestMigrations;
    }

    /**
     *
     * @return void
     * @throws PDOException
     */
    public function undoPrevious(string $migrationName)
    {
        $latestVersion = $this->getLatestVersion();
        $query = "DELETE FROM {$this->getFullTableName()} WHERE version = :latest_version AND migration_name = :migration_name";

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->bindValue(':migration_name', $migrationName);
        $statement->bindValue(':latest_version', $latestVersion);
        $statement->execute();
    }

    public function drop(): void
    {
        $this->getPDOConnection()->exec("DROP TABLE IF EXISTS {$this->getFullTableName()}");
    }

    public function truncate(bool $force = false): void
    {
        if ($force) {
            $this->drop();
            $this->create();
        } else {
            $this->getPDOConnection()->exec("DELETE FROM {$this->getFullTableName()} WHERE 1=1");
        }
    }

    /**
     *
     * @param int $version
     * @return bool
     * @throws PDOException
     */
    public function rollback(int $version): bool
    {
        $query = "DELETE FROM {$this->getFullTableName()} WHERE version = :version";

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->bindValue(':version', $version);

        return $statement->execute();
    }

    /**
     *
     * @return MigrationEntity[]
     * @throws PDOException
     */
    public function getLatestMigrations(): array
    {
        $query = "SELECT * FROM {$this->getFullTableName()} WHERE version = (SELECT MAX(version) AS lastest_version FROM {$this->getFullTableName()}) ORDER BY version DESC, migration_name DESC";

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->execute();

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        return empty($results) ? [] : array_map(function ($migration) {
            return MigrationEntity::fromArray($migration);
        }, $results);
    }

    public function getAll(bool $ascending = false)
    {
        $query = "SELECT * FROM {$this->getFullTableName()} ORDER BY id " . ($ascending ? 'ASC' : 'DESC');

        $statement = $this->getPDOConnection()->prepare($query);
        $statement->execute();

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        return empty($results) ? [] : array_map(function ($migration) {
            return MigrationEntity::fromArray($migration);
        }, $results);
    }
}
