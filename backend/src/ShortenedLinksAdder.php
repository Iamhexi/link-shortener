<?php
require_once 'DatabaseController.php';
require_once 'Logger.php';
require_once 'DatabaseRefusesToAddLinkException.php';
require_once 'UrlFilter.php';
require_once 'MissingParameterException.php';

// adds new links to the database
class ShortenedLinksAdder
{
    use UrlFilter;

    public function createLink(?string $src, ?string $dest): bool
    {
        $db = new DatabaseController();
        $src = $this->filter($src);
        $dest = $this->filter($dest);
        $query = $this->generateUrlInsertingQuery($src, $dest);

        if ( empty($src) )
            throw new MissingParameterException("Parameter 'source' cannot be empty.");
        else if ( empty($dest) )
            throw new MissingParameterException("Parameter 'destination' cannot be empty.");

        if ( !$db->sendQuery($query) )
            throw new DatabaseRefusesToAddLinkException("The database refuses to insert the link between src='$src' and dest='$dest' to the table");
        return true;
    }

    private function generateUrlInsertingQuery(string $src, string $dest): string
    {
        $table = DatabaseController::URL_BINDING_TABLE;
        return "INSERT INTO $table (source, destination) VALUES ('$src', '$dest');";
    }
}
