<?php
class user_group_controller extends general_controller
{
    public function action_index()
    {
        $group_model = new user_group_model();
        $group_list = $group_model->find_all(array(), 'min_exp ASC');
        $n = count($group_list) - 1;
        foreach($group_list as $k => &$v)
        {
            if($k < $n)
            {
                $v['max_exp'] = $group_list[$k + 1]['min_exp'];
            }
            else
            {
                $v['max_exp'] = 9999999999;
            }
        }
        $this->group_list = $group_list;
        $this->compiler('user/group_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'group_name' => trim(request('group_name', '')),
                'min_exp' => (int)request('min_exp', 0),
                'discount_rate' => (int)request('discount_rate', 100),
            );
            
            $group_model = new user_group_model();
            $verifier = $group_model->verifier($data);
            if(TRUE === $verifier)
            {
                $group_model->create($data);
                $this->prompt('success', '添加用户组成功', url($this->MOD.'/user_group', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('user/group.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $group_id = (int)request('id', 0);
            $data = array
            (
                'group_name' => trim(request('group_name', '')),
                'min_exp' => (int)request('min_exp', 0),
                'discount_rate' => (int)request('discount_rate', 100),
            );
            
            $group_model = new user_group_model();
            $group = $group_model->find(array('group_id' => $group_id));
            if($group['min_exp'] == 0 && $data['min_exp'] != 0) $this->prompt('error', '缺少经验值下限为 0 的用户组', url($this->MOD.'/user_group', 'index'));
            $rule_slices = array();
            if($group['min_exp'] == $data['min_exp']) $rule_slices['min_exp'] = FALSE; //如未修改经验值下限
            
            $verifier = $group_model->verifier($data, $rule_slices);
            if(TRUE === $verifier)
            {
                $condition = array('group_id' => $group_id);
                if($group_model->update($condition, $data) > 0) $this->prompt('success', '更新用户组成功', url($this->MOD.'/user_group', 'index'));
                $this->prompt('error', '更新用户组失败');
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $group_model = new user_group_model();
            if($this->rs = $group_model->find(array('group_id' => (int)request('id', 0))))
            {
                $this->compiler('user/group.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
 
    public function action_delete()
    {
        $id = (int)request('id', 0);
        $group_model = new user_group_model();
        if($group = $group_model->find(array('group_id' => $id)))
        {
            if($group['min_exp'] == 0) $this->prompt('error', '不能删除经验值下限为 0 的用户组', url($this->MOD.'/user_group', 'index'));
            $group_model->delete(array('group_id' => $id));
            $this->prompt('success', '删除用户组成功', url($this->MOD.'/user_group', 'index'));
        }
        $this->prompt('error', '删除用户组失败');
    }
}