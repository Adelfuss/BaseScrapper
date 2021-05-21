<?php
namespace Http;

class RequestSender
{
    private $searchString;
    private $cookies = [];
    private $cookieString = '';
    private $scrfTokenValue = '';
    private $response;
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
        CURLOPT_FOLLOWLOCATION => true
    ];
    private $schema = [];
    const GET_CREDENTIALS = HOST_NAME . 'trademarks/search/advanced';
    const GET_TRADE_MARKS = HOST_NAME . 'trademarks/search/doSearch';
    const FIND_COOKIE_REGEXP = '/^Set-Cookie:\s*([^;]*)/mi';
    const CSRF_TOKEN_COOKIE_NAME = 'XSRF-TOKEN';

    public function  __construct($searchString)
    {
        $this->searchString = $searchString;
    }

    /**
     * Facade to do all needed requests to obtain final result(row html)
     * @return $this
     */
    public  function send()
    {
        $this->getCredentials();
        $this->getCsrfToken();
        $this->formCookieString();
        $this->requestParametersSchema();
        $this->buildParams();
        $this->buildHeaders();
        $this->getHtmlContent();
        return $this;
    }

    /**
     * Method to get row data based on send method.
     * @return mixed
     */
    public function get()
    {
        return $this->response;
    }

    /**
     * Perform request to get neccesary cookies and CSRF token form.
     */
    private  function getCredentials()
    {
        $curlObj = curl_init();
        $this->formCurlOptions($curlObj, $this->get_credentials_curl_options);
        $result = curl_exec($curlObj);
        $this->formCookies($result);
        curl_close($curlObj);
    }

    /**
     *  Executing post request and getting list of marks(row html)
     */
    private function getHtmlContent()
    {
        $curl = curl_init();
        $this->formCurlOptions($curl, $this->get_trade_marks_curl_options);
        $this->response = curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Helper to set all curl options in requests.
     * @param $curl
     * @param $optionsArray
     */
    private function formCurlOptions(&$curl, $optionsArray)
    {
        foreach ($optionsArray as $key => $value) {
            curl_setopt($curl, $key, $value);
        }
    }

    /**
     * Forming array with cookie which will be send in post request
     * @param $result
     */
    private  function formCookies($result)
    {
        preg_match_all(self::FIND_COOKIE_REGEXP ,$result,  $match_found);
        foreach($match_found[1] as $item) {
            parse_str($item,  $cookie);
            $this->cookies = array_merge($this->cookies,  $cookie);
        }
    }


    /**
     * Set csrf token in proper property.
     */
    private function getCsrfToken()
    {
        $this->scrfTokenValue = $this->cookies[self::CSRF_TOKEN_COOKIE_NAME];
    }

    /**
     * Forming cookie string to pass in headers for post request.
     */
    private  function formCookieString()
    {
        foreach ($this->cookies as $key => $value) {
            $this->cookieString .= $key . '=' . $value . ';';
        }
    }


    /**
     * Forming parameters for post request
     */
    private  function requestParametersSchema()
    {
        $this->schema =  [
            'wv' => [
                $this->searchString
            ],
            '_csrf' => $this->scrfTokenValue
        ];
    }


    /**
     * Helper to process receiving post data
     */
    private function buildParams()
    {
        $this->get_trade_marks_curl_options[CURLOPT_POSTFIELDS] = http_build_query($this->schema);
    }


    /**
     * Helper to form header to pass needed cookie.
     */
    private  function buildHeaders()
    {
        $this->get_trade_marks_curl_options[CURLOPT_HTTPHEADER] =  array('Cookie: ' . $this->cookieString);
    }

}