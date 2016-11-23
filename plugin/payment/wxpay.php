<?php
/**
 * WxPay Payment
 * @author Cigery
 * Unfinished
 */
class wxpay extends abstract_payment
{
    public function create_pay_url($args)
    {
        //unfinished
    }
    
    public function set_js_params($args)
    {
        $params = array
        (
            'appId' => $this->config['appid'],
            'timeStamp' => (string)$_SERVER['REQUEST_TIME'],
            'nonceStr' => random_chars(32),
            'package' => "prepay_id={$args['prepay_id']}",
            'signType' => 'MD5',
        );
        return $params;
    }
    
    private function _get_openid()
    {
        if(!isset($_GET['code']))
        {
            $params = array
            (
                'appid' => $this->config['appid'],
                'redirect_uri' => urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']),
                'response_type' => 'code',
                'scope' => 'snsapi_base',
                'state' => 'STATE#wechat_redirect',
            );
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$this->_set_params($params);
			
        }
        else
        {
            $params = array
            (
                'appid' => $this->config['appid'],
                'secret' => $this->config['secret'],
                'code' => $_GET['code'],
                'grant_type' => 'authorization_code',
            );
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?'.$this->_set_params($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $res = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($res, TRUE);
            return $res['openid'];
        }
    }
    
    private function _get_prepayid($args)
    {
        $params = array
        (
            'body' => $args['body'],
            'out_trade_no' => $args['out_trade_no'],
            'total_fee' => $args['total_fee'],
            'notify_url' => $this->baseurl. '/api/pay/notify/wxpay',
            'trade_type' => 'JSAPI',
            'spbill_create_ip' => get_ip(),
        );
        
        $xml = $this->_array_to_xml($params);
        $res = $this->_post_xml('https://api.mch.weixin.qq.com/pay/unifiedorder', $xml);
        $res = $this->_xml_to_array($res);
        if(!empty($res['prepay_id'])) return $res['prepay_id'];
        return FALSE;
    }
    
    private function _array_to_xml($array)
	{
    	$xml = '<xml>';
    	foreach($array as $k => $v)
    	{
            if(is_numeric($v))
            {
                $xml .= '<'.$k.'>'.$v.'</'.$k.'>';
            }
            else
            {
                $xml .= '<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }
    
    private function _xml_to_array($xml)
    {
        libxml_disable_entity_loader(TRUE);
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);
        return $array;
    }
    
    private function _post_xml($url, $xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    
    private function _set_params($params)
    {
        $args = '';
        foreach($params as $k => $v)
        {
            if($k != 'sign') $args .= $k.'='.$v.'&';
        }
        return trim($args, '&');
    }
}