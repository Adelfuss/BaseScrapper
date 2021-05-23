<?php

use Helpers\ArgvHandler;
use Helpers\Scraper;
use Http\RequestSender;

require_once dirname(__FILE__) . '/configs/config.php';

error_reporting(DEBUG_MODE);

require_once VENDOR . '/autoload.php';

// argv parameters handler via two mods(Array and Object)
$argvHandler = new  ArgvHandler($argv);

$requestSender = new RequestSender($argvHandler->searchString);

// send request to get needed html page
$rowTradeMarks = $requestSender->send()->get();

// scrapping row html
$htmlDOM = phpQuery::newDocument($rowTradeMarks);

$scrapper = new Scraper($htmlDOM);

// getting parse data in array or json mode
$result =  $scrapper->scrap()->result();

print_r($result);
print_r($scrapper->getCount());












