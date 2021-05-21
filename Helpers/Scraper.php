<?php
namespace Helpers;

class Scraper
{
    private $htmlDOM;
    private $result = [];
    const IS_JSON_MODE = true;
    private $scrappingSchema = [
        'id' => [
            'selector_rule' => 'tbody tr td.table-index span',
            'method' => 'text'
        ],
        'number' => [
            'selector_rule' => 'tbody tr td.number a',
            'method' => 'text'
        ],
        'details_page_url' => [
            'selector_rule' => 'tbody tr td.number a',
            'method' => ['attr:href']
        ],
        'logo_url' => [
            'selector_rule' => 'tbody tr td.trademark.image img',
            'method' => ['attr:src']
        ],
        'name' => [
            'selector_rule' => 'tbody tr td.trademark.words',
            'method' => 'text',
        ],
        'classes' => [
            'selector_rule' => 'tbody tr td.classes',
            'method' => 'text'
        ],
        'status' => [
            'selector_rule' => 'tbody tr td.status span',
            'method' => 'text'
        ]
    ];

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
        foreach ($this->scrappingSchema as $key => $schema) {
            $elements = $this->htmlDOM->find($schema['selector_rule']);
            $this->outputResultHelper($elements, $schema['method'], $key);
        }
        return $this;
    }


    /**
     * Helper to processed all collections elements
     * @param $elements
     * @param $method
     * @param $columnName
     */
    private function outputResultHelper($elements, $method, $columnName)
    {
        $startArrayCount = 0;
        foreach ($elements as $element) {
            $elementItem = pq($element);
            $this->result[$startArrayCount][$columnName] = $this->stripParameters($elementItem, $method);
            $startArrayCount++;
        }
    }


    /**
     * Helper to call needed query selector method for some types of element collections.
     * @param $elem
     * @param $method
     * @return mixed
     */
    private function stripParameters($elem, $method)
    {
        $isAttributeQueryMethod = (is_array($method)) ? true : false;
        if ($isAttributeQueryMethod) {
            $methodOptions = array_shift($method);
            $methodOptions = explode(':', $methodOptions);
            return $elem->{$methodOptions[0]}($methodOptions[1]);
        }
        return $elem->{$method}();
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