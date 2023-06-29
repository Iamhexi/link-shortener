<?php

enum IndexPageLocation: int
{
    case MainDirectory = 1; // index.php file in the main directory
    case Subdirectory = 2; // index.php file in the subdirectory
}