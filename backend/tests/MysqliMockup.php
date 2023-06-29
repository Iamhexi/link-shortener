<?php
require_once 'src/DatabaseConfiguration.php';

class MysqliResultMockup extends mysqli_result
{
    private array $container;

    public function __construct(array $content)
    {
        $this->container = $content;
        //$this->num_rows = count($content);
    }

    public function __get(string $name): int
    {
        if ($name === 'num_rows')
            return count($this->container);
    }

    public function fetch_all($result_type = NULL): array
    {
        switch ($result_type)
        {
            case MYSQLI_NUM:
                return $this->container;
            case MYSQLI_ASSOC:
                return array_values($this->container);
        }
    }
}

class MysqliMockup extends mysqli
{
    private bool $correctlyConfigured;
    private array $container;

    public function __construct(
        ?string $server = null,
        ?string $user = null,
        ?string $password = null,
        ?string $dbName = null
    )
    {
        $this->correctlyConfigured =
               DatabaseConfiguration::SERVER === $server &&
               DatabaseConfiguration::USERNAME === $user &&
               DatabaseConfiguration::PASSWORD === $password &&
               DatabaseConfiguration::DATABASE_NAME === $dbName;

        $this->container[] = ['source' => 'test_source0', 'destination' => 'test_destination0'];
        $this->container[] = ['source' => 'test_source1', 'destination' => 'test_destination1'];
        $this->container[] = ['source' => 'test_source2', 'destination' => 'test_destination2'];

    }

    public function set_charset($charset)
    {
        return in_array($charset, ['utf8', 'utf8mb4', 'binary']);
    }

    public function query($query, $resultmode = NULL)
    {

        if (!$this->correctlyConfigured)
            return false;

        $keywords = explode(' ', $query);

        switch ($keywords[0]) {
            case 'SELECT':
            case 'select':
                return $this->handleSelectQuery([$keywords[1], $keywords[2]]);
                break;

            case 'DELETE':
            case 'delete':
                return $this->handleDeleteQuery($keywords[4], $keywords[6]);
                break;

            case 'INSERT':
            case 'insert':
                $source = str_replace('(', '', $keywords[6] );
                $source = str_replace("'", '', $source);
                $destination = str_replace(");", '', $keywords[7]);
                $destination = str_replace("'", '', $destination);
                return $this->handleInsertQuery($source, $destination);
                break;

            default:
                return false;
        }
    }

    private function handleSelectQuery(
        array $fieldNames,
        ?string $whereField = null,
        ?string $whereCondition = null
    )
    {
        if ($whereField !== null)
            foreach ($this->container as $link) {
                if (!array_key_exists($whereField, $link))
                    return false;
                if ($link[$whereField] === $whereCondition)
                    $output[] = $link;
            }
        else
            return new MysqliResultMockup($this->container);
    }

    private function handleDeleteQuery(string $whereField, string $whereCondition): bool
    {
        if ($whereField !== null)
            foreach ($this->container as $link) {
                if (!array_key_exists($whereField, $link))
                    return false;

                if ($link[$whereField] === $whereCondition)
                    unset($link[$whereField]);

            }

            return true;
    }

    private function handleInsertQuery(string $source, string $destination): bool
    {
        $link = ['source' => $source, 'destination' => $destination];
        $this->container[] = $link;
        return true;
    }
}
