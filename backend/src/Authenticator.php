<?php
require_once 'PageConfiguration.php';

class Authenticator
{
    public static function isPasswordCorrect(?string $password): bool
    {
        return $password === PageConfiguration::PASSWORD;
    }
}
