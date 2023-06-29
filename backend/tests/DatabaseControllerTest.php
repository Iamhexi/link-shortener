<?php
require_once 'src/DatabaseController.php';
require_once 'tests/MysqliMockup.php';

use PHPUnit\Framework\TestCase;

final class DatabaseControllerTest extends TestCase
{

    public function testConnectingWithIncorrectDatabaseCredentials()
    {
        $connection = new MysqliMockup('incorrectServer', '', '', '');
        $db = new DatabaseController($connection);
        $query = "incorrectly configured database";

        $this->assertFalse( $db->sendQuery($query) );
    }

    public function testSendInsertQuery(): void
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "INSERT INTO url_map (source, destination) VALUES ('TEST_SRC', 'TEST_DST');";

        $this->assertTrue( $db->sendQuery($query) );
    }

    public function testSendCorrectDeleteQuery(): void
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "DELETE FROM url_map WHERE source = 'test_source0';";

        $this->assertTrue( $db->sendQuery($query) );
    }

    public function testSendIncorrectDeleteQuery(): void
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "completely incorrect SQL query";

        $this->assertFalse( $db->sendQuery($query) );
    }

    public function testSendCorrectSelectQuery(): void
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = 'SELECT source, destination FROM url_map;';

        $this->assertTrue( $db->sendQuery($query) );
    }

    private function prepareProperlyConfiguredController(): DatabaseController
    {
        $connection = new MysqliMockup(
            DatabaseConfiguration::SERVER,
            DatabaseConfiguration::USERNAME,
            DatabaseConfiguration::PASSWORD,
            DatabaseConfiguration::DATABASE_NAME
        );
        return new DatabaseController($connection);
    }

    public function testGetArrayOfRecordsWhenThereAreNumerous(): void
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "SELECT source, destination FROM url_map;";

        $this->assertGreaterThan( 1, count($db->getArrayOfRecords($query)) );
    }

    public function countMatchingRecordsWhenThereAreNone(?string $query): int
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "SELECT source, destination FROM url_map WHERE source = 'non-existing';";

        $this->assertEquals( 0, $db->countMatchingRecords($query) );
    }

    public function countMatchingRecordsWhenThereIsOne(?string $query): int
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "SELECT source, destination FROM url_map WHERE source = 'test_source0';";

        $this->assertEquals( 1, $db->countMatchingRecords($query) );
    }

    public function countMatchingRecordsWhenThereAreNumerous(?string $query): int
    {
        $db = $this->prepareProperlyConfiguredController();

        $query = "SELECT source, destination FROM url_map";

        $this->assertGreaterThan( 1, $db->countMatchingRecords($query) );
    }

}
