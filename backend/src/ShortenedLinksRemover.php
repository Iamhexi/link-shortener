<?php
require_once 'UrlFilter.php';
require_once 'DatabaseController.php';
require_once 'Logger.php';

class ShortenedLinksRemover
{
    use UrlFilter;

    public function remove(?string $source): bool
    {
        $db = new DatabaseController();
        try {
            $source = $this->filter($source);
            $query = $this->generateQuery($source);

            if (!$db->sendQuery($query))
                throw new Exception("Could NOT remove the link with the source address '$source' from the database");
            return true;

        } catch (Exception $e){
            Logger::report($e);
            return false;
        }
    }

    private function generateQuery(string $source): string
    {
        $table = DatabaseController::URL_BINDING_TABLE;
        return "DELETE FROM $table WHERE source = '$source';";
    }

}
