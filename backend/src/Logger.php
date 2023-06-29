<?php
require_once 'PageConfiguration.php';

class Logger {
    private const LOGGER_FILE = 'logs.txt';

    public static function report(Exception $e): void 
    {
        if (PageConfiguration::IN_DEVELOPMENT === false)
            self::saveExceptionToFile($e);
        else
            echo self::getExceptionAsString($e);
    }

    private static function getExceptionAsString(Exception $e): string 
    {
        $output = 'Error: ';
        $output .= $e->getMessage();
        $output .= ' in the file ';
        $output .= $e->getFile();
        $output .= ' in the line ';
        $output .= $e->getLine().'.'.PHP_EOL;
        return $output;
    }

    private static function saveExceptionToFile(Exception $e): void 
    {
        file_put_contents(
            self::LOGGER_FILE,
            self::getExceptionAsString($e),
            FILE_APPEND | LOCK_EX
        );
    }
}
