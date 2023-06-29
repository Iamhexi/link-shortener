<?php
require_once 'Controller.php';
require_once 'InvalidDatabaseQueryExpection.php';
require_once 'Logger.php';
require_once 'DatabaseConfiguration.php';


class DatabaseController implements Controller
{
    public const DATABASE_NAME = 'in_lenses';
    public const URL_BINDING_TABLE = 'url_map';
    private ?mysqli $connection = null;

    public function __construct(?mysqli $connection = null)
    {
        if ($connection !== null)
            $this->connection = $connection;
        else
            $this->connection = new mysqli(
                DatabaseConfiguration::SERVER,
                DatabaseConfiguration::USERNAME,
                DatabaseConfiguration::PASSWORD,
                DatabaseConfiguration::DATABASE_NAME,
            );

        $this->connection->set_charset('utf8');
    }

    public function sendQuery(?string $query): bool
    {
        try {
            if ( !$this->connection->query($query) )
                throw new InvalidDatabaseQueryExpection("Could NOT perform the query = \"$query\" on the database.");
            return true;
        } catch (Exception $e){
            Logger::report($e);
            return false;
        }
    }

    public function countMatchingRecords(string $query): int
    {
        try {
            if ( !($result = $this->connection->query($query)) )
                throw new InvalidDatabaseQueryExpection("Could NOT perform the query = \"$query\" on the database.");
            return $result->num_rows;
        } catch(InvalidDatabaseQueryExpection $e) {
            Logger::report($e);
            return -1;
        } catch (Exception $e) {
            Logger::report($e);
            return -1;
        }
    }

    public function getArrayOfRecords(string $query): array
    {
        try {

            if ( !($result = $this->connection->query($query)) )
                throw new InvalidDatabaseQueryExpection("Could NOT perform the query = \"$query\" on the database.");

            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (InvalidDatabaseQueryExpection $e){
            Logger::report($e);
            return [];
        } catch (Exception $e){
            Logger::report($e);
            return [];
        }
    }

}
