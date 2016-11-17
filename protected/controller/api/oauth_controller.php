<?php
class oauth_controller extends general_controller
{
    public function action_callback()
    {
        $args = array();
        foreach($_GET as $k => $v)
        {
            if(in_array($k, array('m', 'c', 'a'))) continue;
            $args[$k] = $v;
        }
        
        if(is_mobile_device()) jump(url('mobile/oauth', 'bind', $args));
        jump(url('oauth', 'bind', $args));
    }
    
    public function action_bind()
    {
        if(empty($_SESSION['OAUTH']['KEY'])) die('Bad Request');
        
        $row['user_id'] = $this->is_logined();
        $row['party'] = sql_escape(request('party'));

        $oauth_model = new user_oauth_model();
        if(!$oauth_model->find($row))
        {
            $row['oauth_key'] = $_SESSION['OAUTH']['KEY'];
            if($oauth_model->create($row))
            {
                unset($_SESSION['OAUTH']);
                $res = array('status' => 'success');
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '绑定账号失败, 请稍后重试');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '该账户已绑定过了, 不能重复绑定');
        }
        
        echo json_encode($res);
    }
    
    public function action_unbind()
    {
        $user_id = $this->is_logined();
        $oauth_model = new user_oauth_model();
        if($oauth_model->delete(array('user_id' => $user_id, 'party' => sql_escape(request('party')))) > 0)
        {
            $res = array('status' => 'success');
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '解除绑定失败');
        }
        echo json_encode($res);
    }

}