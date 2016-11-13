<?php
class consignee_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        $this->consignee_list = $consignee_model->get_user_consignee_list($user_id);
        $count = count($this->consignee_list);
        $this->total = array
        (
            'saved' => $count,
            'remaining' => $GLOBALS['cfg']['user_consignee_limits'] - $count,
        );
        $this->compiler('user_consignee_list.html');
    }

    public function action_add()
    {
        $user_id = $this->is_logined();
        $this->compiler('user_consignee.html');
    }
    
    public function action_edit()
    {
        $user_id = $this->is_logined();
        $id = (int)request('id', 0);
        $consignee_model = new user_consignee_model();
        if($this->consignee = $consignee_model->find(array('id' => $id, 'user_id' => $user_id)))
        {
            $this->compiler('user_consignee.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
    public function action_info()
    {
        if($user_id = $this->is_logined())
        {
            $res = array();
            $consignee_model = new user_consignee_model();
            if($res['data'] = $consignee_model->find(array('id' => request('id'), 'user_id' => $user_id)))
            {
                $res['status'] = 'success';
                echo json_encode($res);
            }
            else
            {
                echo json_encode(array('status' => 'error', 'data' => '未查询到该收件人信息'));
            }
        }
        else
        {
            echo json_encode(array('status' => 'error', 'data' => '您尚未登录或登录超时'));
        }
    }
    
    public function action_delete()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        if($consignee_model->delete(array('id' => (int)request('id', 0), 'user_id' => $user_id)) > 0)
        {
            $this->prompt('success', '删除收件人地址成功', url('consignee', 'index'));
        }  
        else
        {
            $this->prompt('error', '删除收件人地址失败');
        }
    }
}