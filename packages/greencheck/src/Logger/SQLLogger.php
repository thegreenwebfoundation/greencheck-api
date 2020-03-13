<?php

namespace TGWF\Greencheck\Logger;

use Doctrine\DBAL\Logging\SQLLogger as SQLLoggerInterface;

/**
 * A SQL logger that logs queries to be countable.
 */
class SQLLogger implements SQLLoggerInterface
{
    protected $queries = [];

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null): void
    {
        $this->queries[] = [$sql, $params, $types];
        /*echo $sql . PHP_EOL;

        if ($params) {
            var_dump($params);
    	}

        if ($types) {
            var_dump($types);
        }*/
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function stopQuery(): void
    {
    }

    /**
     * Clear the queries array to start over.
     */
    public function clearQueries(): void
    {
        $this->queries = [];
    }
}
