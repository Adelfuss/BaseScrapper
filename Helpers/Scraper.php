<?php
namespace Helpers;

class Scraper
{
    private $htmlDOM;
    private $result = [];
    private $columns = [];
    private $totalCount;
    private $columnBase = 'tbody[data-mark-id="%s"] %s';
    private $countSelector = '.number.qa-count';
    private $columnRule = [
        'selector' => 'table tbody tr td.number a',
        'method' => 'text'
    ];
    private $columnElemSchema = [
        'id' => [
            'selector' => 'tr td.table-index span',
            'method' => 'text'
        ],
        'number' => [
            'selector' => 'tr td.number a',
            'method' => 'text'
        ],
        'logo_img' => [
            'selector' => 'tr td.trademark.image img',
            'method' => ['attr:src']
        ],
        'name' => [
            'selector' => 'tr td.trademark.words',
            'method' => 'text'
        ],
        'classes' => [
            'selector' => 'tr td.classes',
            'method' => 'text'
        ],
        'status' => [
            'selector' => 'tr td.status span',
            'method' => 'text'
        ]
    ];
    const IS_JSON_MODE = true;

    public function __construct($htmlDOM)
    {
        $this->htmlDOM = $htmlDOM;
    }

    /**
     * Facade to scrap all needed elements. Rules of scraping are described in $scrappingSchema
     * @return $this
     */
    public function scrap()
    {
        $this->getColumns();
        $this->formResult();
        $this->parseCount();
        return $this;
    }

    private function getColumns()
    {
        $elements = $this->htmlDOM->find($this->columnRule['selector']);
        foreach($elements as $element) {
            $elemItem = pq($element);
            $this->columns[] = $elemItem->{$this->columnRule['method']}();
        }
    }

    private function formResult()
    {
        foreach($this->columns as $key => $column) {
            foreach($this->columnElemSchema as $schema => $schemaData) {
                $selector = sprintf($this->columnBase, $column, $schemaData['selector']);
                $element = $this->htmlDOM->find($selector);
                if (is_array($schemaData['method'])) {
                    $methodComponents = explode(':', array_shift($schemaData['method']));
                    $this->result[$key][$schema] = $element->{$methodComponents[0]}($methodComponents[1]);
                    continue;
                }
                $this->result[$key][$schema] = $element->{$schemaData['method']}();
            }
        }
    }

    private function parseCount()
    {
        $this->totalCount = $this->htmlDOM->find($this->countSelector)->text();
        echo $this->totalCount;
    }

    public function getCount()
    {
        return $this->totalCount;
    }

    /**
     * Helper to return array of json string of scrapped data
     * @return array|false|string
     */
    public function result()
    {
        return (self::IS_JSON_MODE) ? json_encode($this->result,  JSON_PRETTY_PRINT) : $this->result;
    }

}