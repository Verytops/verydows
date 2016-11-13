<?php
/**
 * OAuth2.0 Sina Weibo
 * @author Cigery
 */
class weibo extends abstract_oauth
{
    private $_api = 'https://api.weibo.com';
    
    public function create_login_url($state)
    {
        $params = array
        (
            'client_id' => $this->config['app_key'],
            'client_secret' => $this->config['app_secret'],
            'redirect_uri' => baseurl().'/api/oauth/callback/weibo',
            'response_type' => 'code',
            'state' => $this->set_session('STATE', $state),
            'display' => 'default'
        );
        if($this->device == 'mobile') $params['display'] = 'mobile';
        return $this->_api.'/oauth2/authorize?'.http_build_query($params);
    }
    
    public function check_callback($args)
    {
        if(empty($args['state']) || $args['state'] != $this->get_session('STATE')) return FALSE;
        
        $params = array
        (
            'client_id' => $this->config['app_key'],
            'client_secret' => $this->config['app_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => baseurl().'/api/oauth/callback/weibo',
            'code' => isset($args['code']) ? $args['code'] : '',
        );
        
        $uri = $this->_api.'/oauth2/access_token';
        if($response = $this->curl($uri, $params))
        {
            $res = json_decode($response, TRUE);
            if(!isset($res['error']) && !empty($res['access_token'])) return $res['access_token'];
        }
        return FALSE;
    }
    
    public function get_oauth_key($access_token)
    {
        $uri = $this->_api.'/2/account/get_uid.json?access_token='.$access_token;
        if($response = file_get_contents($uri))
        {
            $res = json_decode($response, TRUE);
            return $res['uid'];
        }
        return FALSE;
    }
    
    public function get_user_info($access_token, $oauth_key)
    {
        $params = array
        (
            'access_token' => $access_token,
            'uid' => $$oauth_key,
        );
        
        $uri = $this->_api.'/2/users/show.json?'.http_build_query($params);
        if($res = file_get_contents($uri))
        {
            $res = json_decode($res, TRUE);
            if($res['gender'] == 'm') $res['gender'] = 1; elseif($res['gender'] == 'f') $res['gender'] = 2; else $res['gender'] = 0;
            return array
            (
                'nickname' => $res['screen_name'],
                'gender' => $res['gender'],
                'avatar' => $res['avatar_large'],
            );
        }
        return FALSE;
    }
}