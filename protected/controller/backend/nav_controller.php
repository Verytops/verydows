<?php
class nav_controller extends general_controller
{
    public function action_index()
    {
        $nav_model = new nav_model();
        $this->pos_map = $nav_model->pos_map;
        $this->results = $nav_model->find_all(null, 'position ASC');
        $this->compiler('setting/nav_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'link' => trim(request('link', '')),
                'position' => (int)request('position', 0),
                'target' => (int)request('target', 0),
                'seq' => (int)request('seq', 99),
                'visible' => (int)request('visible', 0),
            );
            
            $nav_model = new nav_model();
            $verifier = $nav_model->verifier($data);
            if(TRUE === $verifier)
            {
                $nav_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加导航成功', url($this->MOD.'/nav', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $nav_model = new nav_model();
            $this->pos_map = $nav_model->pos_map;
            $this->compiler('setting/nav.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'link' => trim(request('link', '')),
                'position' => (int)request('position', 0),
                'target' => (int)request('target', 0),
                'seq' => (int)request('seq', 99),
                'visible' => (int)request('visible', 0),
            );
            
            $nav_model = new nav_model();
            $verifier = $nav_model->verifier($data);
            if(TRUE === $verifier)
            {
                $nav_model->update(array('id' => (int)request('id')), $data);
                $this->clear_cache();
                $this->prompt('success', '更新导航成功', url($this->MOD.'/nav', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
            
        }
        else
        {
            $nav_model = new nav_model();
            if($this->rs = $nav_model->find(array('id' => (int)request('id'))))
            {
                $this->pos_map = $nav_model->pos_map;
                $this->compiler('setting/nav.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_delete()
    {
        $id = (array)request('id');
        if(!empty($id))
        {
            $affected = 0;
            $nav_model = new nav_model();
            foreach($id as $v) $affected += $nav_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个记录, 失败 {$failure} 个", url($this->MOD.'/nav', 'index'));
        }
        else
        {
            $this->prompt('error', '无法获取参数');
        }
    }
    
    //清除缓存
    private function clear_cache()
    {
        vcache::instance()->nav_model('get_site_nav', null, -1);
    }
}