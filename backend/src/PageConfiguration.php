<?php
require_once 'IndexPageLocation.php';

class PageConfiguration
{
    public const IN_DEVELOPMENT = true;
    public const INDEX_PAGE_LOCATION = IndexPageLocation::Subdirectory;
    public const LOGIN_PAGE = 'loginPage.html';
    public const PASSWORD = '';
    public const URL = 'http://localhost:8081';
    public const ADMIN_EMAIL = 'admin@localhost.pl';
    public const FRONTEND_URL = '*';
}
