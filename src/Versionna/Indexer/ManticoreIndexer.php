<?php

namespace Sharpen\Versionna\Indexer;

use PDO;
use PDOStatement;
use Sharpen\Versionna\Manticore\ManticoreConnection;
use Sharpen\Versionna\Storage\DatabaseConnection;

// TODO: Implement the Indexer
class ManticoreIndexer extends Indexer
{
    /**
     * @var ManticoreConnection
     */
    protected $manticoreConnection;

    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    // public function getRepopulateQuery(): string { }

    public function countQueryResults(PDOStatement|bool $queryResult): int
    {
        return $queryResult ? $queryResult->rowCount() : 0;
    }

    public function index(string $toIndex, string $query, array $indexFields = ['*'], int $chunkSize = 100, bool $repopulate = true): bool
    {
        // $count = $this->databaseConnection->getConnection()->query($query)->execute();	// TODO: Implement the count() method on the query object
        // $page = 0;
        // $pages = ceil($count / $chunkSize);
        $page = 0;
        $lastCount = $chunkSize;
        $dbConnection = $this->databaseConnection->getConnection();
        $manticoreClient = $this->manticoreConnection->getClient();
        $manticoreIndex = $manticoreClient->index($toIndex);

        while ($lastCount > 0 && $lastCount <= $chunkSize) {
            $offset = $page * $chunkSize;
            $queryResult = $dbConnection->query("{$query} LIMIT {$chunkSize} OFFSET {$offset}");

            $lastCount = $this->countQueryResults($queryResult);

            $results = $queryResult->fetchAll(PDO::FETCH_OBJ);

            $manticoreIndex->addDocuments($results);

            $page++;
        }

        return true;
    }
}
