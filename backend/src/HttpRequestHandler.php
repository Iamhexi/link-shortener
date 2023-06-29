<?php
require_once 'API.php';
require_once 'URLParser.php';

session_start();

class HttpRequestHandler
{
    private API $api;
    private string $requestMethod;  

    private const LINK_MODULE = 'link';
    private const AUTH_MODULE = 'auth';
    private const MAIL_MODULE = 'mail';
    private const USER_MODULE = 'user';

    private const REDIRECTION_MODULE = 'l';


    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->api = new API;
    }

    public function isResponseSent(): bool
    {
        return $this->api->isResponseSent();
    }

    public function handleRequest(): void
    {

        $parameters = URLParser::retrieveUrlParametersAsAssocArray();
        $token = $parameters['token'] ?? null;
        
        $path = URLParser::retrieveUrlAsArray();

        $modules = [
            self::AUTH_MODULE, self::LINK_MODULE,
            self::MAIL_MODULE, self::USER_MODULE,
            self::REDIRECTION_MODULE
        ];


        foreach($modules as $module)
        {
            if (array_search($module, $path) === false)
                continue;
            else {
                $index = array_search($module, $path);
                @$source = $path[ $index + 1 ];
            }
           
        }
        
        if (@$path[$index] === self::AUTH_MODULE)
            $this->handleAuthAssociatedAction($token);
        else if (@$path[$index] === self::LINK_MODULE)
            $this->handleLinkAssociatedAction($token);
        else if (@$path[$index] === self::MAIL_MODULE)
            $this->handleMailAssociatedAction();
        else if(@$path[$index] === self::USER_MODULE)
            $this->handleUserAssociatedAction($token);
    }

    private function handleLinkAssociatedAction(?string $token): void
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $source = @$input['source'];

        switch ($this->requestMethod)
        {
            case 'GET':
                $this->api->getLink($token, $source);
                break;

            case 'POST':
                $this->api->addLink($token, $source, $input['destination']);
                break;

            case 'DELETE':
                $this->api->removeLink($token, $source);
                break;
        }
    }

    private function handleAuthAssociatedAction(?string $token): void
    {
        switch ($this->requestMethod)
        {
            case 'GET':
                $this->api->logout();
                break;

            case 'POST':
                $input = (array) json_decode(file_get_contents('php://input'), true);
                $this->api->login($input['password']);
                break;

            default:
                $this->api->reportUnrecognisedRequestMethod();
            }

    }

    private function handleMailAssociatedAction(): void
    {
        switch ($this->requestMethod) 
        {
            case 'POST':
                $input = (array) json_decode(file_get_contents('php://input'), true);
                $this->api->sendMail($input['sender'], $input['subject'], $input['content']);
                break;

            default:
                $this->api->reportUnrecognisedRequestMethod();
        }
    }

    private function handleUserAssociatedAction(?string $token): void
    {
        switch($this->requestMethod)
        {
            case 'GET':
                $this->api->isUserSignedIn($token);
                break;

            default:
                $this->api->reportUnrecognisedRequestMethod();
        }
    }
}
