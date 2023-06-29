<?php

class URLParser
{
    public static function retrieveUrlAsArray(): array
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $output = explode('/', $uri) ?? [];
        unset($output[0]);
        return array_values($output);
    }

    public static function retrieveUrlParametersAsAssocArray(): array
    {
        $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($query == null)
            return [];
        $parameters = explode('&', $query);

        foreach ($parameters as $param)
        {
            $param = explode('=', $param);
            $paramName = $param[0];
            $paramValue = $param[1];
            $output[ $paramName ] = $paramValue;
        }

        return $output ?? [];
    }
}
