<?php
class goods_optional_type_controller extends general_controller
{
    public function action_index()
    {
        $type_model = new goods_optional_type_model();
        $this->results = $type_model->find_all(array(), 'type_id DESC', '*', array(request('page', 1), 15));
        $this->paging = $type_model->page;
        $this->compiler('goods/optional_type_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array('name' => trim(request('name', '')));
            $type_model = new goods_optional_type_model();
            $verifier = $type_model->verifier($data);
            if(TRUE === $verifier)
            {
                $type_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加选项类型成功', url($this->MOD.'/goods_optional_type', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('goods/optional_type.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $type_id = (int)request('id', 0);
            $data = array('name' => trim(request('name', '')));
            $type_model = new goods_optional_type_model();
            $verifier = $type_model->verifier($data);
            if(TRUE === $verifier)
            {
                if($type_model->update(array('type_id' => $type_id), $data) > 0) $this->clear_cache();
                $this->prompt('success', '更新选项类型成功', url($this->MOD.'/goods_optional_type', 'index'));  
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $type_model = new goods_optional_type_model();
            if($this->rs = $type_model->find(array('type_id' => (int)request('id', 0))))
            {
                $this->compiler('goods/optional_type.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $condition = array('type_id' => (int)request('id', 0));
        $type_model = new goods_optional_type_model();
        if($type_model->delete($condition) > 0)
        {
            $opt_model = new goods_optional_model();
            $opt_model->delete($condition);
            $this->clear_cache();
            $this->prompt('success', '删除选项类型成功', url($this->MOD.'/goods_optional_type', 'index'));
        }
        else
        {
            $this->prompt('error', '删除选项类型失败');
        }
    }
    
    //清除缓存
    private static function clear_cache()
    {
        return vcache::instance()->goods_optional_type_model('indexed_list', null, -1);
    }
}