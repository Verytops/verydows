<?php
class general_controller extends Controller
{
    protected function is_logined()
    {
        if(isset($_SESSION['USER']['USER_ID'])) return $_SESSION['USER']['USER_ID'];
        die(json_encode(array('status' => 'unlogined', 'msg' => '您还未登陆或登录超时')));
    }
}