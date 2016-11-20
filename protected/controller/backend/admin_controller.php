<?php
class admin_controller extends general_controller
{
    public function action_index()
    {
        $admin_model = new admin_model();
        $this->results = $admin_model->find_all(null, 'user_id DESC', '*', array(request('page', 1), 15));
        $this->paging = $admin_model->page;
        $this->compiler('admin/admin_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'username' => trim(request('username', '')),
                'password' => trim(request('password', '')),
                'repassword' => trim(request('repassword', '')),
                'email' => trim(request('email', '')),
                'name' => trim(request('name', '')),
                'created_date' => $_SERVER['REQUEST_TIME'],
            );
            
            $admin_model = new admin_model();
            $verifier = $admin_model->verifier($data);
            if(TRUE === $verifier)
            {
                $data['password'] = md5e($data['password']);
                $data['hash'] = sha1(uniqid(rand(), TRUE));
                unset($data['repassword']);
                if($user_id = $admin_model->create($data))
                {
                    $role_ids = request('role_ids', null, 'post');
                    if(!empty($role_ids) && is_array($role_ids))
                    {
                        $admin_role_model = new admin_role_model();
                        foreach($role_ids as $v) $admin_role_model->create(array('user_id' => $user_id, 'role_id' => $v));
                    }
                    $this->clear_cache();
                }
                $this->prompt('success', '添加管理员成功', url($this->MOD.'/admin', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $role_model = new role_model();
            $this->role_list = $role_model->find_all(null, null, 'role_id, role_name');
            $this->compiler('admin/admin.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $user_id = request('id');
            $condition = array('user_id' => $user_id);
            $data = array
            (
                'username' => trim(request('username', '')),
                'password' => trim(request('password', '')),
                'repassword' => trim(request('repassword', '')),
                'email' => trim(request('email', '')),
                'name' => trim(request('name', '')),
            );
            
            $rule_slices = array();
            $admin_model = new admin_model();
            $user = $admin_model->find($condition);
            if($user['username'] == $data['username'])
            {
                 $rule_slices['username'] = FALSE;
                 unset($data['username']);
            }
            if(empty($data['password']) && empty($data['repassword']))
            {
                $rule_slices['password'] = $rule_slices['repassword'] = FALSE;
                unset($data['password']);
            }
             
            $verifier = $admin_model->verifier($data, $rule_slices);
            if(TRUE === $verifier)
            {
                if(isset($data['password'])) $data['password'] = md5e($data['password']);
                unset($data['repassword']);
                $admin_model->update($condition, $data);
                
                $admin_role_model = new admin_role_model();
                $admin_role_model->delete($condition);
                $role_ids = (array)request('role_ids');
                if(!empty($role_ids))
                {
                    $admin_role_model = new admin_role_model();
                    foreach($role_ids as $v) $admin_role_model->create(array('user_id' => $user_id, 'role_id' => (int)$v));
                }
                    
                $this->clear_cache();
                $this->prompt('success', '更新管理员成功', url($this->MOD.'/admin', 'index'));        
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $user_id = (int)request('id', 0);
            $condition = array('user_id' => $user_id);
            $admin_model = new admin_model();
            if($rs = $admin_model->find($condition))
            {
                $admin_role_model = new admin_role_model();
                $rs['role_ids'] = $admin_role_model->find_all($condition, null, 'role_id');
                if(!empty($rs['role_ids']))
                {
                    $rs['role_ids'] = array_column($rs['role_ids'], 'role_id');
                }
                $this->rs = $rs;
                $role_model = new role_model();
                $this->role_list = $role_model->find_all(null, null, 'role_id, role_name');
                $this->compiler('admin/admin.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_delete()
    {
        $id = request('id');
        if($id == 1) $this->prompt('error', '此管理员用户不能被删除');

        $admin_model = new admin_model();
        if($admin_model->delete(array('user_id' => $id)) > 0) $this->prompt('success', '删除管理员成功', url($this->MOD.'/admin', 'index'));
        $this->prompt('error', '删除管理员失败'); 
    }
    
    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->admin_model('indexed_list', null, -1);
    }

}