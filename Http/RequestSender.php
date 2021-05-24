<?php
namespace Http;

class RequestSender
{
    private $searchString;
    private $cookies = [];
    private $cookieString = '';
    private $scrfTokenValue = '';
    private $searchQuery = '';
    private $paginationCount;
    private $paginationParser;
    private $pages;
    private $get_credentials_curl_options = [
        CURLOPT_URL => self::GET_CREDENTIALS,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ];
    private $get_trade_marks_curl_options = [
        CURLOPT_URL => self::GET_TRADE_MARKS,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [],
        CURLOPT_POSTFIELDS => '',
        CURLOPT_FOLLOWLOCATION => true,
    ];
    private $get_pages_content_options = [
        CURLOPT_URL => '%s?s=%s&p=%s',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ];
    private $schema = [];
    const GET_CREDENTIALS = HOST_NAME . 'trademarks/search/advanced';
    const GET_TRADE_MARKS = HOST_NAME . 'trademarks/search/doSearch';
    const GET_CONTENT_PAGE = HOST_NAME . 'trademarks/search/result';
    const FIND_COOKIE_REGEXP = '/^Set-Cookie:\s*([^;]*)/mi';
    const CSRF_TOKEN_COOKIE_NAME = 'XSRF-TOKEN';

    public function  __construct($searchString, $paginationParser)
    {
        $this->searchString = $searchString;
        $this->paginationParser = $paginationParser;
    }

    /**
     * Facade to do all needed requests to obtain final result(row html)
     * @return $this
     */
    public  function send()
    {
        $this->getScrappingData();
        $this->getHtmlPages();
        return $this;
    }

    private function getScrappingData()
    {
        $this->getCredentials();
        $this->getCsrfToken();
        $this->formCookieString();
        $this->requestParametersSchema();
        $this->buildParams('get_trade_marks_curl_options');
        $this->buildHeaders('get_trade_marks_curl_options');
    }

    private function getHtmlPages()
    {
        $this->getQueryResources();
        $this->loadPagesContent();
    }

    
    public function get()
    {
        return $this->pages;
    }

   
    private  function getCredentials()
    {
        $curlObj = curl_init();
        $this->formCurlOptions($curlObj, $this->get_credentials_curl_options);
        $result = curl_exec($curlObj);
        $this->formCookies($result);
        curl_close($curlObj);
    }

    
    private function getQueryResources()
    {
        $curl = curl_init();
        $this->formCurlOptions($curl, $this->get_trade_marks_curl_options);
        $this->getResultQueryString(curl_exec($curl));
        $this->getPaginationCount();
        curl_close($curl);
    }

    private function loadPagesContent()
    {
        for($i = 0; $i < $this->paginationCount; $i++) {
        $curl = curl_init();
        $this->buildUrl('get_pages_content_options',$i);
        $this->buildHeaders('get_pages_content_options');
        $this->formCurlOptions($curl, $this->get_pages_content_options);
        $this->pages[$i] = curl_exec($curl);
        curl_close($curl);
        $this->clearUrlOption('get_pages_content_options', '%s?s=%s&p=%s');
        } 
    }

    private function getResultQueryString($response)
    {
        $this->searchQuery = $this->paginationParser->getResultString($response);
    }

    private function getPaginationCount()
    {
        $this->paginationCount = $this->paginationParser->getPagesCount();
    }

    
    private function formCurlOptions(&$curl, $optionsArray)
    {
        foreach ($optionsArray as $key => $value) {
            curl_setopt($curl, $key, $value);
        }
    }

    
    private  function formCookies($result)
    {
        preg_match_all(self::FIND_COOKIE_REGEXP ,$result,  $match_found);
        foreach($match_found[1] as $item) {
            parse_str($item,  $cookie);
            $this->cookies = array_merge($this->cookies,  $cookie);
        }
    }

    private function getCsrfToken()
    {
        $this->scrfTokenValue = $this->cookies[self::CSRF_TOKEN_COOKIE_NAME];
    }

    
    private  function formCookieString()
    {
        foreach ($this->cookies as $key => $value) {
            $this->cookieString .= $key . '=' . $value . ';';
        }
    }


   
    private  function requestParametersSchema()
    {
        $this->schema =  [
            'wv' => [
                $this->searchString
            ],
            '_csrf' => $this->scrfTokenValue
        ];
    }


    
    private function buildParams($optionArrayName)
    {
        $this->{$optionArrayName}[CURLOPT_POSTFIELDS] = http_build_query($this->schema);
    }

    private function clearUrlOption($optionArrayName, $primaryValue)
    {
        $this->{$optionArrayName}[CURLOPT_URL] = $primaryValue;
    }

    private  function buildHeaders($optionArrayName)
    {
        $this->{$optionArrayName}[CURLOPT_HTTPHEADER] =  array('Cookie: ' . $this->cookieString);
    }

    private function buildUrl($optionArrayName, $id) 
    {
        $this->{$optionArrayName}[CURLOPT_URL] = sprintf($this->{$optionArrayName}[CURLOPT_URL], self::GET_CONTENT_PAGE, $this->searchQuery, $id);
    }

}