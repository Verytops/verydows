<?php
class email_subscription_controller extends general_controller
{
    public function action_index()
    {
        if(request('step') == 'search')
        {
            $conditions = array();
            $status = request('status', '');
            $email = request('email', '');
            if($status != '') $conditions['status'] = (int)$status; 
            if($email != '') $conditions['email'] = $email;
            
            $email_model = new email_subscription_model();
            if($list = $email_model->find_all($conditions, 'id DESC', '*', array(request('page', 1), request('pernum', 1))))
            {
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $email_model->page,
                );
            }
            else
            {
                $results = array('status' => 'nodata');
            }
            
            echo json_encode($results);
        }
        else
        {
            $email_model = new email_subscription_model();
            $this->status_map = $email_model->status_map;
            $this->compiler('email/subscription_list.html');
        }
    }
    
    public function action_status()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $status = (int)request('status', 0);
            $affected = 0;
            $email_model = new email_subscription_model();
            foreach($id as $v) $affected += $email_model->update(array('id' => (int)$v), array('status' => $status));
            $failures = count($id) - $affected;
            $handle = $status == 1 ? '确认' : '退订';
            $this->prompt('default', "成功{$handle} {$affected} 个订阅邮箱, 失败 {$failures} 个", url($this->MOD.'/email_subscription', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $email_model = new email_subscription_model();
            foreach($id as $v) $affected += $email_model->delete(array('id' => (int)$v));
            $failures = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 条订阅邮箱, 失败 {$failures} 条", url($this->MOD.'/email_subscription', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }

}