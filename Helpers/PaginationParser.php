<?php 
namespace Helpers;

use phpQuery;

class PaginationParser 
{
    private $htmlDOM;
    private $content;
    private $totalCount;
    private $countSelector = '.number.qa-count';
    const RESULT_QUERY_STRING_SELECTOR = 'form[action="/trademarks/search/refine"] input[name="s"]';
    const PER_PAGE = 100;

    private function setDocument($document)
    {
        $this->htmlDOM = phpQuery::newDocument($document);
    }

    public function getResultString($response)
    {
        $this->setDocument($response);
        $element = $this->htmlDOM->find(self::RESULT_QUERY_STRING_SELECTOR);
        return $result = pq($element)->attr('value');
    }

    public function getPagesCount()
    {
        $marksAmount = $this->getTotalCount();
        $totalPagesCount = ceil($marksAmount / self::PER_PAGE);
        return $totalPagesCount;
    }

    public function getTotalCount()
    {
        $this->totalCount = $this->htmlDOM->find($this->countSelector)->text();
        return str_replace(",",'',$this->totalCount);
    }

}