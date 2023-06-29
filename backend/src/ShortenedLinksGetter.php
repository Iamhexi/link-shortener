<?php
require_once 'DatabaseController.php';
require_once 'Logger.php';
require_once 'DatabaseRefusesToAddLinkException.php';

// retrieves one or all records from DB
class ShortenedLinksGetter
{
    public function get(?string $source): array
    {
        $db = new DatabaseController();
        try {

            if ($source === null)
                throw new Exception('The source of the sought url cannot be null');
            $query = $this->generateQueryGettingOneRecord($source);
            return $db->getArrayOfRecords($query);

        } catch (Exception $e){
            Logger::report($e);
            return [];
        }
    }

    public function getAll(): array
    {
        $db = new DatabaseController();
        try {

            $query = $this->generateQueryGettingAllRecords();
            return $db->getArrayOfRecords($query);

        } catch (Exception $e){
            Logger::report($e);
            return [];
        }
    }

    private function generateQueryGettingOneRecord(string $source): string
    {
        $table = DatabaseController::URL_BINDING_TABLE;
        return "SELECT source, destination FROM $table WHERE source = '$source';";
    }

    private function generateQueryGettingAllRecords(): string
    {
        $table = DatabaseController::URL_BINDING_TABLE;
        return "SELECT source, destination FROM $table;";
    }
}
