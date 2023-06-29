<?php

trait UrlFilter
{
    private function filter(?string $url): string
    {
        if ($url === null)
            throw new Exception('The provided url is empty. Therefore, it cannot be filtered.');
        else
            return filter_var($url, FILTER_SANITIZE_URL);
    }
}