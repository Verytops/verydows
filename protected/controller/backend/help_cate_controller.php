<?php
class help_cate_controller extends general_controller
{
    public function action_index()
    {
        $cate_model = new help_cate_model();
        $this->results = $cate_model->find_all(null, 'cate_id ASC');
        $this->compiler('article/help_cate_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'cate_name' => trim(request('cate_name', '')),
                'seq' => (int)request('seq', 99),
            );

            $cate_model = new help_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                $cate_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加帮助分类成功', url($this->MOD.'/help_cate', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('article/help_cate.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'cate_name' => trim(request('cate_name', '', 'post')),
                'seq' => (int)request('seq', 99, 'post'),
            );
            
            $cate_model = new help_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                if($cate_model->update(array('cate_id' => (int)request('id', 0)), $data) > 0)
                {
                    $this->clear_cache();
                    $this->prompt('success', '更新帮助分类成功', url($this->MOD.'/help_cate', 'index'));
                }   
                else
                {
                    $this->prompt('error', '更新帮助分类失败');
                }
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new help_cate_model();
            if($this->rs = $cate_model->find(array('cate_id' => (int)request('id', 0))))
            {
                $this->compiler('article/help_cate.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $cate_model = new help_cate_model();
        if($cate_model->delete(array('cate_id' => (int)request('id', 0))) > 0)
        {
            $this->clear_cache();
            $this->prompt('success', '删除帮助分类成功', url($this->MOD.'/help_cate', 'index'));
        }   
        else
        {
            $this->prompt('error', '删除帮助分类成失败');
        }
    }
    
    //清除缓存
    private static function clear_cache()
    {
        $vcache = vcache::instance();
        $vcache->help_cate_model('indexed_list', null, -1);
        $vcache->help_model('cated_help_list', null, -1);
    }
}