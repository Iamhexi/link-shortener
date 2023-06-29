<?php
require_once 'NotExistentUrlException.php';
require_once 'UrlFilter.php';
require_once 'DatabaseController.php';
require_once 'PageConfiguration.php';

// handles redirection from a shortended url to the destination
class ShortenedLinksHandler
{
    use UrlFilter;

    private const MISSING_PAGE_URL = 'missingPage.php';
    private const FAILURE_URL = 'unknownFailure.php';
    private ?string $redirectTo = null;

    public function redirectToDestination(?string $shortenedUrl, bool $responded): void
    {
        try {
            if ($responded)
                return;
            
            $shortenedUrl = $this->filter($shortenedUrl);  

            if ( !$this->exists($shortenedUrl) )
                throw new NotExistentUrlException("The provided url = '$shortenedUrl' does NOT exist in the database");
            $this->redirectTo = $this->retrieveDestinationUrl($shortenedUrl);

        } catch(NotExistentUrlException $e) {
            Logger::report($e);
            $this->redirectTo = PageConfiguration::URL.'/'.self::MISSING_PAGE_URL;

        } catch (Exception $e) {
            Logger::report($e);
            $this->redirectTo = PageConfiguration::URL.'/'.self::FAILURE_URL;
        } finally {
            if ($this->redirectTo !== null)
                header("location: $this->redirectTo");
            exit();
        }

    }

    private function retrieveDestinationUrl(string $sourceUrl): string
    {
        try {
            $db = new DatabaseController();
            $query = $this->generateUrlCountingQuery($sourceUrl);
            $rows = $db->getArrayOfRecords($query);

            if (count($rows) == 0)
            {
                Logger::report( new Warning("No matching url for '$sourceUrl'") );
                return '';
            }

            return $rows[0]['destination'];
        } catch (Exception $e){
            Logger::report($e);
            return '';
        }
    }

    private function exists(?string $sourceUrl): bool
    {
        $query = $this->generateUrlCountingQuery($sourceUrl);
        $db = new DatabaseController();
        if ( $db->countMatchingRecords($query) >= 1)
            return true;
        return false;
    }

    private function generateUrlCountingQuery(?string $sourceUrl): string
    {
        $table = DatabaseController::URL_BINDING_TABLE;
        return "SELECT source, destination FROM $table WHERE source = '$sourceUrl';";
    }
}
