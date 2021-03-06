<?php

/*
* Request
* This classes handles the HTTP requests responsible
* for making calls to the Locu API.
*/

namespace LocuPHP;

class Request
{
    /**
     * Base URL
     */
    const API_URL = 'http://api.locu.com/';

    /**
     * API Version
     * Must be set to 'v1_0' for version 1 (deprecated)
     * and 'V2' for version 2
     */
    private $_api_version = 'v2';

    /**
     * API Key
     */
    private $_api_key;


    /**
     * Constructor
     *
     * @param array $base_url
     * @return void
     */
    public function __construct($config)
    {
        //@todo - write test
        if (!is_array($config)) {
            throw new Exception("Configuration:  Missing configuration.");
        }

        //@todo - write test
        if (!isset($config['api_key']) || $config['api_key'] == '') {
            throw new Exception("Configuration: Missing API Key.");
        }

        $this->_setApiKey($config['api_key']);
        if(!empty($config['api_version']))
            $this->_setApiVersion($config['api_version']);
    }

    /**
     * API Key Setter
     *
     * @param string  $api_key
     * @return void
     */
    private function _setApiKey($api_key)
    {
        $this->_api_key = $api_key;
    }

    /**
     * API Version Setter
     *
     * @param string  $api_version
     * @return void
     */
    private function _setApiVersion($api_version)
    {
        $this->_api_version = $api_version;
    }


    /**
     * API Key Getter
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_api_key;
    }

     /**
     * Make an api request
     *
     * @param string $resource
     * @param array $params
     * @param string $method
     */
    public function call($resource, $params = array())
    {

        $params['api_key'] = $this->_getApiKey();

        $json = json_encode($params);

        $requestUrl = self::API_URL . self::API_VERSION;

        $requestUrl .= '/' . $resource;

        $curl = curl_init();

        $curl_options = array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => $requestUrl,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTPHEADER => array('Accept: application/json'),
          CURLOPT_POST => 1,
          CURLOPT_POSTFIELDS => $json
        );

        curl_setopt_array($curl, $curl_options);
        $response = curl_exec($curl);
        $curl_info = curl_getinfo($curl);

        //@todo test for curl error
        if ($response === FALSE) {
            throw new Exception(curl_error($curl), curl_errno($curl));
        }
        curl_close($curl);

        //@todo test for any non 200 response
        if ($curl_info['http_code'] != 200) {
            throw new Exception("Response: Bad response - HTTP Code:". $curl_info['http_code']);
        }

        $jsonArray = json_decode($response);

        if (!is_object($jsonArray)) {
             throw new Exception("Response: Response was not a valid response");
        }

        return $jsonArray;
    }

}
