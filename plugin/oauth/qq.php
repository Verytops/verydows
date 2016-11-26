<?php
/**
 * OAuth2.0 Tencent QQ
 * @author Cigery
 */
class qq extends abstract_oauth
{
    private $_api = 'https://graph.qq.com';
    
    public function create_login_url($state)
    {
        $params = array
        (
            'response_type' => 'code',
            'client_id' => $this->config['app_id'],
            'redirect_uri' => baseurl().'/api/oauth/callback/qq',
            'state' => $this->set_session('STATE', $state),
            'scope' => 'get_user_info',
        );
        if($this->device == 'mobile') $params['display'] = 'mobile';
        return $this->_api.'/oauth2.0/authorize?'.http_build_query($params);
    }
    
    public function check_callback($args)
    {
        if(empty($args['state']) || $args['state'] != $this->get_session('STATE') || empty($args['code'])) return FALSE;
        
        $params = array
        (
            'grant_type' => 'authorization_code',
            'client_id' => $this->config['app_id'],
            'redirect_uri' => baseurl().'/api/oauth/callback/qq',
            'client_secret' => $this->config['app_key'],
            'scope' => 'get_user_info',
            'code' => $args['code'],
        );
        
        $uri = $this->_api.'/oauth2.0/token?'.http_build_query($params);
        if($str = file_get_contents($uri))
        {
            if(strpos($str, 'callback') !== FALSE)
            {
                $lpos = strpos($str, "(");
                $rpos = strrpos($str, ")");
                $str = substr($str, $lpos + 1, $rpos - $lpos -1);
            }
            
            $res = array();
            parse_str($str, $res);
            if(!empty($res['access_token'])) return $res['access_token'];
        }
        return FALSE;
    }
    
    public function get_oauth_key($access_token)
    {
        $uri = $this->_api.'/oauth2.0/me?access_token='.$access_token;
        $res = file_get_contents($uri);
        if(strpos($res, 'callback') !== FALSE)
        {
            $lpos = strpos($res, "(");
            $rpos = strrpos($res, ")");
            $res = substr($res, $lpos + 1, $rpos - $lpos -1);
        }
        $res = json_decode($res, TRUE);
        if(empty($res['code']) && !empty($res['openid']) && !empty($res['client_id']) && $res['client_id'] == $this->config['app_id'])
        {
            return $res['openid'];
        }
        return FALSE;
    }
    
    public function get_user_info($access_token, $oauth_key)
    {
        $params = array
        (
            'oauth_consumer_key' => $this->config['app_id'],
            'access_token' => $access_token,
            'openid' => $oauth_key,
            'format' => 'json',
        );
        
        $uri = $this->_api.'/user/get_user_info?'.http_build_query($params);
        if($res = file_get_contents($uri))
        {
            $res = json_decode($res, TRUE);
            if($res['gender'] == '男') $res['gender'] = 1; elseif($res['gender'] == '女') $res['gender'] = 2; else $res['gender'] = 0;
            return array
            (
                'nickname' => $res['nickname'],
                'gender' => $res['gender'],
                'avatar' => $res['figureurl_qq_2'],
            );
        }
        return FALSE;
    }
}