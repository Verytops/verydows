<?php
class setting_controller extends general_controller
{
    public function action_index()
    {   
        $setting_model = new setting_model();
        $rs = $setting_model->get_config();
        if($rs['upload_goods_filetype']) $rs['upload_goods_filetype'] = implode('|', $rs['upload_goods_filetype']);
        $rs['themes'] = $this->get_available_themes();
        $rs['enabled_theme'] = $this->get_enabled_theme();
        $this->rs = $rs;
        $this->compiler('setting/index.html');
    }

    public function action_update()
    {
        switch(request('step'))
        {
            case 'global':
            
                $data = array
                (
                    'site_name' => trim(request('site_name', '')),
                    'encrypt_key' => trim(request('encrypt_key', '')),
                    'debug' => (int)request('debug', 0),
                    'visitor_stats' => (int)request('visitor_stats', 0),
                    'admin_mult_ip_login' => (int)request('admin_mult_ip_login', 0),
                    'data_cache_lifetime' => (int)request('data_cache_lifetime', 0),
                    'footer_info' => trim(stripslashes(request('footer_info', ''))),
                );

            break;
            
            case 'home':
            
                $data = array
                (
                    'home_title' => trim(request('home_title', '')),
                    'home_keywords' => request('home_keywords', ''),
                    'home_description' => request('home_description', ''),
                );
                
            break;
       
            case 'goods':
            
                $data = array
                (
                    'goods_hot_searches' => trim(request('goods_hot_searches', '')),
                    'goods_fulltext_query' => (int)request('goods_fulltext_query', 0),
                    'cate_goods_per_num' => (int)request('cate_goods_per_num', 10),
                    'goods_search_per_num' => (int)request('goods_search_per_num', 10),
                    'goods_history_num' => (int)request('goods_history_num', 5),
                    'goods_related_num' => (int)request('goods_related_num', 5),
                    'goods_review_per_num' => (int)request('goods_review_per_num', 10),
                    'show_goods_stock' => (int)request('show_goods_stock', 0),
                    'upload_goods_filetype' => trim(request('upload_goods_filetype', '')),
                    'upload_goods_filesize' => trim(request('upload_goods_filesize', '')),
                    'goods_img_thumb' => array(),
                    'goods_album_thumb' => array(),
                );
                
                if($thumb_img = request('goods_img_thumb'))
                {
                    if($thumb_img_arr = array_combine($thumb_img['w'], $thumb_img['h']))
                    {
                        $goods_img_thumb = array();
                        foreach($thumb_img_arr as $k => $v)
                        {
                            $goods_img_thumb[] = array('w' => (int)$k, 'h' => (int)$v);
                        }
                        $data['goods_img_thumb'] = json_encode($goods_img_thumb);
                    }
                }
                
                if($thumb_album = request('goods_album_thumb'))
                {
                    if($thumb_album_arr = array_combine($thumb_album['w'], $thumb_album['h']))
                    {
                        $goods_album_thumb = array();
                        foreach($thumb_album_arr as $k => $v)
                        {
                            $goods_album_thumb[] = array('w' => (int)$k, 'h' => (int)$v);
                        }
                        $data['goods_album_thumb'] = json_encode($goods_album_thumb);
                    }
                }
            
            break;
            
            case 'user':
                
                $data = array
                (
                    'user_register_email_verify' => (int)request('user_register_email_verify', 0),
                    'user_review_approve' => (int)request('user_review_approve', 0),
                    'upload_avatar_filesize' => trim(request('upload_avatar_filesize', '')),
                    'user_consignee_limits' => (int)request('user_consignee_limits', 0),
                    'order_cancel_expires' => (float)request('order_cancel_expires', 1),
                    'order_delivery_expires' => (int)request('order_delivery_expires', 7),
                );
                
            break;
            
            case 'rewrite':
                
                $data['rewrite_enable'] = (int)request('rewrite_enable', 0);
                $data['rewrite_rule'] = array();
                $rule = request('rewrite_rule', null);
                if(is_array($rule) && $rule_arr = array_combine($rule['k'], $rule['v']))
                {
                    foreach($rule_arr as $k => $v)
                    {
                        if(!empty($k) && !empty($v)) $data['rewrite_rule'][trim($k)] = trim($v);
                    }
                    $data['rewrite_rule'] = json_encode($data['rewrite_rule']);
                }
            
            break;
            
            case 'mail':
            
                $data = array
                (
                    'smtp_server' => trim(request('smtp_server', '')),
                    'smtp_port' => (int)request('smtp_port', 25),
                    'smtp_user' => trim(request('smtp_user', '')),
                    'smtp_password' => trim(request('smtp_password', '')),
                    'smtp_secure' => request('smtp_secure', ''),
                );
                
            break;
            
            case 'captcha':
            
                $data = array
                (
                    'captcha_admin_login' => (int)request('captcha_admin_login', 0),
                    'captcha_user_login' => (int)request('captcha_user_login', 0),
                    'captcha_user_register' => (int)request('captcha_user_register', 0),
                    'captcha_feedback' => (int)request('captcha_feedback', 0),
                );
                
            break;
            
            case 'theme':
                
                $data = array('enabled_theme' => request('theme', ''));
                
            break;
            
            case 'other':
            
                $data = array
                (
                    'upload_filetype' => trim(request('upload_filetype', '')),
                    'upload_filesize' => trim(request('upload_filesize', '')),
                );
            
            break;
        }
        
        $setting_model = new setting_model();
        foreach($data as $k => $v) $setting_model->update(array('sk' => $k), array('sv' => $v));
        if($setting_model->update_config()) $this->prompt('success', '更新设置成功', url($this->MOD.'/setting', 'index'));
        $this->prompt('error', '更新设置失败');
    }
    
    public function action_test_sendmail()
    {
        include(APP_DIR.DS.'plugin'.DS.'phpmailer'.DS.'PHPMailerAutoload.php');
        $mailer = new PHPMailer();
        $smtp_user = trim(request('smtp_user', ''));
        $mailer->isSMTP();
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPAuth = TRUE;                 
        $mailer->Host = trim(request('smtp_server', ''));
        $mailer->Port = trim(request('smtp_port', ''));
        $mailer->Username = $smtp_user;
        $mailer->Password = trim(request('smtp_password', ''));
        $mailer->SMTPSecure = request('smtp_secure', '');
        $mailer->SetFrom($smtp_user, $GLOBALS['cfg']['site_name']);  
        $mailer->addAddress(trim(request('recipient', '')));
        $mailer->isHTML(FALSE);
        $mailer->Subject = "来自{$GLOBALS['cfg']['site_name']}的测试邮件";
        $mailer->Body = '当您的邮箱收到此封邮件，说明邮件服务器连接正常，可以正常使用邮件发送功能。';
        
        $res = array('status' => 'success');
        if(!$mailer->send()) $res = array('status' => 'error', 'msg' => $mailer->ErrorInfo);
        echo json_encode($res);
    }
    
    //获取当前启用模板主题
    private function get_enabled_theme()
    {
        $theme = include(VIEW_DIR.DS.'frontend'.DS.$GLOBALS['cfg']['enabled_theme'].DS.'config.php');
        $theme['dirname'] = $GLOBALS['cfg']['enabled_theme'];
        return $theme;
    }
    
    //获取所有可用的模板主题
    private function get_available_themes()
    {
        $path = VIEW_DIR.DS.'frontend';
        $scanned = array_diff(scandir($path), array('..', '.', 'index.html'));
        $themes = array();
        foreach($scanned as $v)
        {
            $config_file = $path.DS.$v.DS.'config.php';
            if(@is_file($config_file))
            {
                $config = include($config_file);
                $themes[] = $config + array('dirname' => $v);
            }
        }
        return $themes;
    }
}