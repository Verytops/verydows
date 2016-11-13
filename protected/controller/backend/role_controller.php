<?php
class role_controller extends general_controller
{
    public function action_index()
    {
        $role_model = new role_model();
        $this->results = $role_model->find_all(null, 'role_id DESC');
        $this->compiler('admin/role_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'role_name' => trim(request('role_name', '')),
                'role_desc' => trim(request('role_desc', '')),
                'role_acl' => request('role_acl', ''),
            );
            
            $role_model = new role_model();
            $verifier = $role_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($data['role_acl']) && is_array($data['role_acl'])) $data['role_acl'] = json_encode($data['role_acl']);
                $role_model->create($data);
                $this->prompt('success', '添加角色成功', url($this->MOD.'/role', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->uri_list = include(INCL_DIR.DS.'sys_uri.php');
            $this->compiler('admin/role.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'role_name' => trim(request('role_name', '')),
                'role_desc' => trim(request('role_desc', '')),
                'role_acl' => request('role_acl', ''),
            );
            
            $role_model = new role_model();
            $verifier = $role_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($data['role_acl']) && is_array($data['role_acl'])) $data['role_acl'] = json_encode($data['role_acl']);
                $role_model->update(array('role_id' => (int)request('id', 0)), $data);
                $this->prompt('success', '更新角色成功', url($this->MOD.'/role', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $role_model = new role_model();
            if($rs = $role_model->find(array('role_id' => (int)request('id', 0))))
            {
                if(!empty($rs['role_acl'])) $rs['role_acl'] = json_decode($rs['role_acl'], TRUE);
                $this->rs = $rs;
                $this->uri_list = include(INCL_DIR.DS.'sys_uri.php');
                $this->compiler('admin/role.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_delete()
    {
        $condition = array('role_id' => (int)request('id', 0));
        $role_model = new role_model();
        if($role_model->delete($condition) > 0)
        {
            $admin_role_model = new admin_role_model();
            $admin_role_model->delete($condition);
            $this->prompt('success', '删除角色成功', url($this->MOD.'/role', 'index'));
        }
        else
        {
            $this->prompt('error', '删除角色失败');
        }
    }
}