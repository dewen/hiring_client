<?php

/**
 * Hiring API REST client
 *
 * support 
 *
 */
class Client
{
    /**
     * API url
     *
     * @var     url     string
     */
    protected $url = '';

    /**
     * version
     *
     * @var     verision     string
     */
    protected $version = '';

    /**
     * default curl options
     *
     * @var     curlOpt
     */
    protected $curlOpt = array(
        CURLOPT_RETURNTRANSFER => true, 
    );

    /**
     * Token for v2
     *
     * @var     token     string
     */
    protected $token = '';

    /**
     * Constructor
     *
     * - check PHP CURL support
     * - set REST API versions
     * - set debug option
     */
    public function __construct($url, $version) 
    {
        if (!function_exists('curl_init'))
            throw new Exception("CURL extension is required.");

        $this->url = $url;
        $this->version = $version;

        if (defined('DEBUG') && DEBUG)
            $this->curlOpt[CURLOPT_VERBOSE] = true;
    }

    /**
     * - get value with given key 
     * - get list of values if key is omitted.
     *
     * @param   key     string      optional
     * @return  string
     * @throws  Exception           if the return status is fail.
     */
    public function get($key = '')
    {
        $url = ($key) ? 
            '/key?key='.$key  :
            '/list?'; 
        $url = $this->_getApiUrl($url);
        $result =  $this->curlSend($url);

        if ($result['data']['status'] == 'ok') {
            return ($key) ? $result['data'][$key] : implode(' ', $result['data']['keys']);
        } else {
            return 'error ' . $result['status'] . ' ' . $result['data']['msg'];
        }
    }

    /**
     * - set the value with given key
     *
     * @param   key         string      input key
     * @param   value       string      input value
     * @return  string
     * @throws  Exception           if the return status is fail.
     */
    public function set($key, $value)
    {
        $url = $this->_getApiUrl('/key?key=' . $key . '&value=' . $value);
        $this->_handler[CURLOPT_CUSTOMREQUEST] = 'POST';

        $result = $this->curlSend($url);
        if ($result['data']['status'] == 'ok') {
            return 'ok';
        } else {
            return 'error ' . $result['status'] . ' ' . $result['data']['msg'];
        }
    }

    /**
     * - delete record with given key
     *
     * @param   key         string      input key
     * @return  string
     * @throws  Exception           if the return status is fail.
     */
    public function delete($key)
    {
        $url = $this->_getApiUrl('/key?key=' . $key);

        $this->_handler[CURLOPT_CUSTOMREQUEST] = 'DELETE';

        $result = $this->curlSend($url);
        if ($result['data']['status'] == 'ok') {
            return 'ok';
        } else {
            return 'error ' . $result['status'] . ' ' . $result['data']['msg'];
        }
    }

    /**
     * - auth request with username and password
     *
     * @param   user    string      user name
     * @param   pass    string      password
     * @return  string
     * @throws  Exception           if the return status is fail.
     */
    public function auth($user, $pass)
    {
        $url = $this->_getApiUrl('/auth?user=' . $user . '&pass=' . $pass);

        $result =  $this->curlSend($url);

        if ($result['data']['status'] == 'ok') {
            $this->token = $result['data']['token'];
            return "ok";
        } else {
            return 'error ' . $result['status'] . ' ' . $result['data']['msg'];
        }
    }

    /**
     * send curl request
     *
     * @param       $curl       resource        
     * @return      string
     */
    public function curlSend($url)
    {
        $curl = $this->curlInit($url);
        if (!($result = curl_exec($curl)))
            throw new Exception('Request failed.');

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
        curl_close($curl); 

        return array('data'=>json_decode($result, true), 'status'=>$status);
    }

    /**
     * initialize the curl call
     *
     * @return  resource
     */
    public function curlInit($url = '')
    {
        $curl = curl_init($url);
        foreach ($this->curlOpt as $key => $val)
            curl_setopt($curl, $key, $val);

        return $curl;
    }

    //* implementation

    protected function _getApiUrl($qs)
    {
        if ($this->token) $qs .= '&token=' . $this->token;

        return $this->url . '/' . $this->version . $qs;
    }
}
