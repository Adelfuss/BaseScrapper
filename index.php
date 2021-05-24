<?php

use Helpers\ArgvHandler;
use Helpers\{Scraper, PaginationParser};
use Http\RequestSender;

require_once dirname(__FILE__) . '/configs/config.php';

error_reporting(DEBUG_MODE);

require_once VENDOR . '/autoload.php';

// argv parameters handler via two mods(Array and Object)
$argvHandler = new  ArgvHandler($argv);


$requestSender = new RequestSender($argvHandler->searchString, new PaginationParser());

// send request to get needed html page
$rowTradeMarks = $requestSender->send()->get();


$scrapper = new Scraper($rowTradeMarks);

// getting parse data in array or json mode
$result =  $scrapper->scrap()->result();

print_r($result);











