<?php
class goods_cate_attr_controller extends general_controller
{
    public function action_index()
    {
        $cate_id = (int)request('cate_id', 0);
        $cate_model = new goods_cate_model();
        if($this->cate = $cate_model->find(array('cate_id' => $cate_id)))
        {
            $attr_model = new goods_cate_attr_model();
            if($attr_list = $attr_model->find_all(array('cate_id' => $cate_id), 'seq ASC'))
            {
                foreach($attr_list as &$v) if(!empty($v['opts'])) $v['opts'] = json_decode($v['opts'], TRUE);
            }
            $this->attr_list = $attr_list;
            $this->compiler('goods/cate_attr_list.html');
        }
        else
        {
            $this->prompt('error', '未找到相应的数据记录');
        }
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $cate_id = (int)request('cate_id', 0);
            $data = array
            (
                'name' => trim(request('name', '')),
                'cate_id' => $cate_id,
                'opts' => request('opts', ''),
                'filtrate' => (int)request('filtrate', 0),
                'uom' => trim(request('uom', '')),
                'seq' => (int)request('seq', 99),
            );
                    
            $attr_model = new goods_cate_attr_model();
            $verifier = $attr_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($data['opts'])) $data['opts'] = json_encode($data['opts']);
                $attr_model->create($data);
                $this->prompt('success', '添加分类属性成功', url($this->MOD.'/goods_cate_attr', 'index', array('cate_id' => $cate_id)));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->cate_id = request('cate_id'); 
            $this->compiler('goods/cate_attr.html');
        }
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $cate_id = (int)request('cate_id', 0);
            $data = array
            (
                'name' => trim(request('name', '')),
                'cate_id' => $cate_id,
                'opts' => request('opts', ''),
                'filtrate' => (int)request('filtrate', 0),
                'uom' => trim(request('uom', '')),
                'seq' => (int)request('seq', 99),
            );
            
            $attr_model = new goods_cate_attr_model();
            $verifier = $attr_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($data['opts'])) $data['opts'] = json_encode($data['opts']);
                $attr_model->update(array('attr_id' => request('id')), $data);
                $this->prompt('success', '更新分类属性成功', url($this->MOD.'/goods_cate_attr', 'index', array('cate_id' => $cate_id)));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $attr_model = new goods_cate_attr_model();
            if($rs = $attr_model->find(array('attr_id' => (int)request('id', 0))))
            {
                if(!empty($rs['opts'])) $rs['opts'] = json_decode($rs['opts'], TRUE);
                $this->rs = $rs;
                $this->compiler('goods/cate_attr.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $condition = array('attr_id' => (int)request('id'));
        $attr_model = new goods_cate_attr_model();
        if($attr_model->delete($condition) > 0)
        {
            $goods_attr_model = new goods_attr_model();
            $goods_attr_model->delete($condition);
            $this->prompt('success', '删除分类属性成功');
        }  
        else
        {
            $this->prompt('error', '删除分类属性');
        }  
    }
}
