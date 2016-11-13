<?php
abstract class abstract_oauth
{
    protected $config = array();
    
    public $error = array();
    
    public $device;
    
    public function __construct($params = null, $mod = '')
    {
        if(!empty($params)) $this->config = json_decode($params, TRUE);
        $this->device = $mod;
    }
    
    abstract protected function create_login_url($state);
    
    abstract protected function check_callback($args);
    
    abstract protected function get_oauth_key($access_token);
    
    abstract protected function get_user_info($access_token, $oauth_key);
    
    protected function curl($uri, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if(version_compare(PHP_VERSION, '5.4.0', '<'))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        }
        else
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ch, CURLOPT_URL, $uri);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    protected function set_session($key, $value)
    {
        $_SESSION['OAUTH'][$key] = $value;
        return $value;
    }
    
    protected function get_session($key)
    {
        if(isset($_SESSION['OAUTH'][$key])) return $_SESSION['OAUTH'][$key];
        return FALSE;
    }
}