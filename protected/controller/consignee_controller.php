<?php
class consignee_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $user_model = new user_model();
        $this->user = $user_model->find(array('user_id' => $user_id));
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

    public function action_save()
    {
        $user_id = $this->is_logined();
        $data = array
        (
            'user_id' => $user_id,
            'receiver' => trim(strip_tags(request('receiver', '', 'post'))),
            'province' => (int)request('province', 0, 'post'),
            'city' => (int)request('city', 0, 'post'),
            'borough' => (int)request('borough', 0, 'post'),
            'address' => trim(strip_tags(request('address', '', 'post'))),
            'zip' => trim(request('zip', '', 'post')),
            'mobile' => trim(request('mobile', '', 'post')),
        );
        
        $consignee_model = new user_consignee_model();
        $verifier = $consignee_model->verifier($data);
        if(TRUE === $verifier)
        {
            if($id = (int)request('id', 0))
            {
                $consignee_model->update(array('id' => $id, 'user_id' => $user_id), $data);
                $this->prompt('success', '更新收件人地址成功', url('consignee', 'list'));
            }
            else
            {
                $consignee_model->create($data);
                $this->prompt('success', '新建收件人地址成功', url('consignee', 'list'));
            }
        }
        else
        {
            $this->prompt('error', $verifier);
        }
    }
    
    public function action_defaulted()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        $consignee_model->update(array('user_id' => $user_id, 'is_default' => 1), array('is_default' => 0));
        $affected = $consignee_model->update(array('id' => (int)request('id', 0), 'user_id' => $user_id), array('is_default' => 1));
        if($affected)
        {
            $this->prompt('success', '设为默认地址成功');
        }
        else
        {
            $this->prompt('error', '设为默认地址失败');
        }
    }
    
    public function action_delete()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        if($consignee_model->delete(array('id' => (int)request('id', 0), 'user_id' => $user_id)) > 0)
        {
            $this->prompt('success', '删除收件人地址成功');
        }  
        else
        {
            $this->prompt('error', '删除收件人地址失败');
        }
    }
}