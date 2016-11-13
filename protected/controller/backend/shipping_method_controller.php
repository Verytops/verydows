<?php
class shipping_method_controller extends general_controller
{
    public function action_index()
    {
        $method_model = new shipping_method_model();
        $this->results = $method_model->find_all(null, 'id DESC');
        $this->compiler('shipping/method_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'instruction' => trim(request('instruction', '')),
                'params' => stripslashes(request('params', '')),
                'seq' => (int)request('seq', 99),
                'enable' => (int)request('enable', 0),
            );

            $method_model = new shipping_method_model();
            $verifier = $method_model->verifier($data);
            if(TRUE === $verifier)
            {
                $method_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加配送方式成功', url($this->MOD.'/shipping_method', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $area = new area();
            $this->area_select = $area->get_children();
            $this->compiler('shipping/method.html');
        }
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'instruction' => trim(request('instruction', '')),
                'params' => stripslashes(request('params', '')),
                'seq' => (int)request('seq', 99),
                'enable' => (int)request('enable', 0),
            );

            $method_model = new shipping_method_model();
            $verifier = $method_model->verifier($data);
            if(TRUE === $verifier)
            {
                $method_model->update(array('id' => (int)request('id')), $data);
                $this->clear_cache();
                $this->prompt('success', '更新配送方式成功', url($this->MOD.'/shipping_method', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $method_model = new shipping_method_model();
            if($this->rs = $method_model->find(array('id' => request('id'))))
            {
                $area = new area();
                $this->area_select = $area->get_children();
                $this->compiler('shipping/method.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $method_model = new shipping_method_model();
        if($method_model->delete(array('id' => (int)request('id'))) > 0)
        {
            $this->clear_cache();
            $this->prompt('success', '删除配送方式成功', url($this->MOD.'/shipping_method', 'index'));
        }    
        else
        {
            $this->prompt('error', '删除配送方式失败');
        }    
    }
    
    //清除缓存
    private function clear_cache()
    {
        vcache::instance()->shipping_method_model('indexed_list', null, -1);
    }
    
}