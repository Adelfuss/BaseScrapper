<?php
namespace Helpers;

use phpQuery;

class Scraper
{
    private $html;
    private $result = [];
    private $columns = [];
    private $columnBase = 'tbody[data-mark-id="%s"] %s';
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

    public function __construct($html)
    {
        $this->html = $html;
    }

    
    public function scrap()
    {
        foreach($this->html as $page) {
            $document = phpQuery::newDocument($page);
            $this->getColumns($document);
            $this->formResult($document);
        }
        return $this;
    }

    private function getColumns($document)
    {
        $elements = $document->find($this->columnRule['selector']);
        foreach($elements as $element) {
            $elemItem = pq($element);
            $this->columns[] = $elemItem->{$this->columnRule['method']}();
        }
    }

    private function formResult($document)
    {
        foreach($this->columns as $key => $column) {
            foreach($this->columnElemSchema as $schema => $schemaData) {
                $selector = sprintf($this->columnBase, $column, $schemaData['selector']);
                $element = $document->find($selector);
                if (is_array($schemaData['method'])) {
                    $methodComponents = explode(':', array_shift($schemaData['method']));
                    $this->result[$key][$schema] = $element->{$methodComponents[0]}($methodComponents[1]);
                    continue;
                }
                $this->result[$key][$schema] = $element->{$schemaData['method']}();
            }
        }
    }

    public function result()
    {
        return (self::IS_JSON_MODE) ? json_encode($this->result,  JSON_PRETTY_PRINT) : $this->result;
    }

}