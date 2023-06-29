<?php
require 'src/HttpRequestHandler.php';
require 'src/ShortenedLinksHandler.php';

//error_reporting(0); // TODO: uncomment before deployment
error_reporting(E_ALL); // TODO: remove before deployment; for development only!
ini_set('display_errors', '1');


$requestHandler = new HttpRequestHandler();
$requestHandler->handleRequest();

$linkHandler = new ShortenedLinksHandler();
$uri = URLParser::retrieveUrlAsArray();

if ( ( $pageSource = @$uri[ PageConfiguration::INDEX_PAGE_LOCATION->value ] ) != null ) 
      $linkHandler->redirectToDestination( $pageSource, $requestHandler->isResponseSent() );
