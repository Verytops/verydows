<?php
class goods_cate_controller extends general_controller
{
    public function action_index()
    {
        $cate_model = new goods_cate_model();
        $this->results = $cate_model->indexed_cate_tree();
        $this->compiler('goods/cate_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'parent_id' => (int)request('parent_id', 0),
                'cate_name' => trim(request('cate_name', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'seq' => (int)request('seq', 99),
            );

            $cate_model = new goods_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                $id = $cate_model->create($data);
                $brands = (array)request('brands', array());
                if(!empty($brands))
                {
                    $cate_brand_model = new goods_cate_brand_model();
                    foreach($brands as $v) $cate_brand_model->create(array('cate_id' => $id, 'brand_id' => (int)$v));
                }
                $this->clear_cache();
                $this->prompt('success', '添加商品分类成功', url($this->MOD.'/goods_cate', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new goods_cate_model();
            $this->parent_list = $cate_model->indexed_cate_tree();
            $brand_model = new brand_model();
            $this->brand_list = $brand_model->indexed_list();
            $this->compiler('goods/cate.html');
        }
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'parent_id' => (int)request('parent_id', 0),
                'cate_name' => trim(request('cate_name', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'seq' => (int)request('seq', 99),
            );
            
            $cate_model = new goods_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                $id = (int)request('id', 0);
                $condition = array('cate_id' => $id);
                $cate_brand_model = new goods_cate_brand_model();
                $cate_brand_model->delete($condition);
                $brands = (array)request('brands', array());
                if(!empty($brands))
                {
                    foreach($brands as $v) $cate_brand_model->create(array('cate_id' => $id, 'brand_id' => (int)$v));
                }
                if($cate_model->update($condition, $data) > 0) $this->clear_cache();
                $this->prompt('success', '更新商品分类成功', url($this->MOD.'/goods_cate', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $id = (int)request('id', 0);
            $cate_model = new goods_cate_model();
            if($this->rs = $cate_model->find(array('cate_id' => $id)))
            {
                $brand_model = new brand_model();
                $brand_list = $brand_model->indexed_list();
                $cate_brand_model = new goods_cate_brand_model();
                $cate_brand_map = $cate_brand_model->find_all(array('cate_id' => $id));
                if(!empty($cate_brand_map))
                {
                    $cate_brand_map = array_column($cate_brand_map, 'brand_id');
                    foreach($brand_list as &$v)
                    {
                        $v['checked'] = in_array($v['brand_id'], $cate_brand_map) ? 'checked="checked"' : '';
                    }
                }
                $this->parent_list = $cate_model->indexed_cate_tree();
                $this->brand_list = $brand_list;
                $this->compiler('goods/cate.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_delete()
    {
        $id = request('id', 0);
        $cate_model = new goods_cate_model();
        if($cate_model->find_count(array('parent_id' => $id)) == 0)
        {
            $condition = array('cate_id' => (int)$id);
            if($cate_model->delete($condition) > 0)
            {
                $cate_brand_model = new goods_cate_brand_model();
                $cate_brand_model->delete($condition);
                $this->clear_cache();
                $this->prompt('success', '删除商品分类成功', url($this->MOD.'/goods_cate', 'index'));
            }  
            else
            {
                $this->prompt('error', '删除商品分类失败');
            }   
        }
        else
        {
            $this->prompt('error', '无法完成删除, 请先移除该分类下的子分类');
        }
    }
    
    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->goods_cate_model('indexed_cate_tree', null, -1);
    }
}