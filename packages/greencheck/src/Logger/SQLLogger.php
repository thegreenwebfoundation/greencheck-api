<?php

namespace TGWF\Greencheck\Logger;

use Doctrine\DBAL\Logging\SQLLogger as SQLLoggerInterface;

/**
 * A SQL logger that logs queries to be countable
 *
 */
class SQLLogger implements SQLLoggerInterface
{
    protected $queries = array();

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queries[] = array($sql, $params, $types);
        /*echo $sql . PHP_EOL;

        if ($params) {
            var_dump($params);
    	}

        if ($types) {
            var_dump($types);
        }*/
    }

    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }

    /**
     * Clear the queries array to start over
     *
     * @return [type] [description]
     */
    public function clearQueries()
    {
        $this->queries = array();
    }
}
