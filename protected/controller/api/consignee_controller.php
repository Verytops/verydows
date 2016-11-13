<?php
class consignee_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        if($list = $consignee_model->get_user_consignee_list($user_id))
        {
            echo json_encode(array('status' => 'success', 'list' => $list));
        }
        else
        {
            echo json_encode(array('status' => 'error'));
        }
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
                $data['id'] = $id;
            }
            else
            {
                $data['id'] = $consignee_model->create($data);
            }
            $res = array('status' => 'success', 'data' => $data);
        }
        else
        {
            $res = array('status' => 'error', 'msg' => $verifier[0]);
        }
        echo json_encode($res);
    }
    
    public function action_defaulted()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        $consignee_model->update(array('user_id' => $user_id, 'is_default' => 1), array('is_default' => 0));
        $affected = $consignee_model->update(array('id' => (int)request('id', 0), 'user_id' => $user_id), array('is_default' => 1));
        if($affected)
        {
            echo json_encode(array('status' => 'success'));
        }
        else
        {
            echo json_encode(array('status' => 'error', 'msg' => '设置失败'));
        }
    }
    
    public function action_delete()
    {
        $user_id = $this->is_logined();
        $consignee_model = new user_consignee_model();
        $affected = $consignee_model->delete(array('id' => (int)request('id', 0), 'user_id' => $user_id));
        if($affected)
        {
            echo json_encode(array('status' => 'success'));
        }
        else
        {
            echo json_encode(array('status' => 'error', 'msg' => '删除失败'));
        }
    }
}