<?php
require 'src/URLParser.php';

final class URLParserTest extends PHPUnit\Framework\TestCase
{
    function testRetrieveUrlAsArray(): void
    {
        $_SERVER['REQUEST_URI'] = 'https://random-page.com/dir/page';
        $actual = URLParser::retrieveUrlAsArray();

        $expected = [ 'dir', 'page' ];
        $this->assertEquals($expected, $actual);
    }

    function testRetrieveUrlParametersAsAssocArray(): void
    {
        $_SERVER['REQUEST_URI'] = 'https://random-page.com?name=john&id=2';
        $actual = URLParser::retrieveUrlParametersAsAssocArray();

        $expected = [
            'name' => 'john',
            'id' => '2'
        ];
        $this->assertEquals($expected, $actual);
    }

}
