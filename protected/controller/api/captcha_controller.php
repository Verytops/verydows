<?php
class captcha_controller extends Controller
{
    public function action_image()
    {
        $captcha = new captcha();
        $captcha->width = 160;
        $captcha->min_word_len = 4;
        $captcha->max_word_len = 4;
        $captcha->session_var = 'CAPTCHA';
        $captcha->line_width = rand(1, 3);
        $captcha->fonts = array
        (
            'Antykwa'  => array('spacing' => rand(1, 5), 'minSize' => 24, 'maxSize' => 28, 'font' => 'AntykwaBold.ttf'),
        );
        $captcha->create_image();
    }
    
    public function action_login()
    {
        $error_model = new request_error_model();
        if($error_model->check(get_ip(), $GLOBALS['cfg']['captcha_user_login']))
        {
            $res = array('enabled' => 1);
        }
        else
        {
            $res = array('enabled' => 0);
        }
        echo json_encode($res);
    }
}