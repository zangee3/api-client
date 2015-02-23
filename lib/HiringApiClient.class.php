<?php
/**
* Hiring API Client Class
*
* @author Ahmed Shams
* @since 2015.02.20
* @copyright Ahmed Shams
*/
class Hiring_API_Client {
    //API base url
    const API_URL = 'http://hiringapi.dev.voxel.net/';
    
    //API Version
    private $_api_version = 'v1';

    //API Auth Token
    private $_auth_token;

    //API Error Message
    private $_api_error;

    /**
     * Validate api command
     * @param string $command
     * @return bool $valid
     * @throws Exception
     */
    public function validate_command($command, $params) {

        // check if command menthod exists in this class, if not throw a command not found exception
        if(!method_exists($this, $command)) {
            throw new Exception( "Error: validate_command() - command not found: '$command'." );
        }

        // get the number of arguments the method is expecting
        $classMethod = new ReflectionMethod($this, $command);
        $argumentCount = count($classMethod->getParameters());

        // if parameters passed count is less than the number of arguments the method is expection,
        //throw a missing parameters exception
        if (count($params) < $argumentCount) {
            throw new Exception( "Error: validate_command() - Missing parameter for action '$command'." );
        }

    }

    /**
     * Set the api version
     * @param string $api_version
     */
    public function set_api_version( $api_version ) {
        $this->_api_version = $api_version;
    }


    /**
     * Authorize a user for api v2
     * @param array $params
     * @return string
     */
    public function auth($params) {
        $endpoint = "/auth";
        $apiParams = array('user' => $params[0], 'pass' => $params[1]);
        $response = $this->_make_api_call($endpoint, $apiParams);
        if('ok' === $response->status) {
            $this->_auth_token = $response->token;
            $return = $response->status;
        } else {
            $return = $this->_api_error;
        }
        return $return;
    }

    /**
     * Set a value for a given key
     * @param array $params
     * @return string
     */
    public function set($params) {
        $endpoint = "/key";
        $apiParams = array('key' => $params[0], 'value' => $params[1]);
        $response = $this->_make_api_call($endpoint, $apiParams, 'POST');
        return ('ok' === $response->status) ? $response->status : 'error ' . $response->msg;
    }

    /**
     * Get a value for a given key
     * @param array $params
     * @return string
     */
    public function get($params) {
        $endpoint = "/key";
        $key = $params[0];
        $apiParams = array('key' => $key);
        $response = $this->_make_api_call($endpoint, $apiParams);
        return ('ok' === $response->status) ? $response->$key : $this->_api_error;

    }

    /**
     * List available keys
     * @return string
     */
    public function listKeys() {
        $endpoint = "/list";
        $response = $this->_make_api_call($endpoint);

        return ('ok' === $response->status) ? implode(' ', $response->keys) : $this->_api_error;
    }

    /**
     * Delete a given key
     * @param array $params
     * @return string
     */
    public function delete($params) {
        $endpoint = "/key";
        $apiParams = array('key' => $params[0]);
        $response = $this->_make_api_call($endpoint, $apiParams, 'DELETE');
        return ('ok' === $response->status) ? $response->status : $this->_api_error;
    }

    /**
     * Make the call to the API
     * @param string $endpoint
     * @param array $apiParams
     * @param string $method
     * @return mixed|json string
     */
    private function _make_api_call( $endpoint, $apiParams = array(), $method = 'GET' ) {
        $ch = curl_init();
        $queryStr = http_build_query($apiParams);
        // Set up the enpoint URL
        $curlURL = $this::API_URL . $this->_api_version . $endpoint . "?" . $queryStr . "&token=" . $this->_auth_token;

        curl_setopt( $ch, CURLOPT_URL, $curlURL);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        if ( 'POST' === $method ) {
            curl_setopt( $ch, CURLOPT_POST, true );
        } else if ( 'DELETE' === $method ) {
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
        }
        $return = curl_exec( $ch );
        $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        // if $return is empty, we hit a curl http error
        if ( empty( $return ) ) {
            $return = '{"status":"failed", "msg":"code: ' . $code . ' cURL HTTP error"}';
        }

        //return as an object
        $return = json_decode( $return );

        //ran into issue where the api is returning bad json string, so catching this here
        if($return === NULL) {
            $return = json_decode('{"status": "fail", "msg": "unknown error"}');
        }

        if(!empty($return->status) && 'fail' === $return->status) {
            $this->_api_error = 'error ' . $return->msg;
        }

        return $return;

    }

}
?>