<?php
class article_cate_controller extends general_controller
{
    public function action_index()
    {
        $cate_model = new article_cate_model();
        $this->results = $cate_model->find_all(null, 'cate_id DESC');
        $this->compiler('article/article_cate_list.html');
    }

    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'cate_name' => trim(request('cate_name', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'seq' => (int)request('seq', 99),
            );

            $cate_model = new article_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                $cate_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加文章分类成功', url($this->MOD.'/article_cate', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('article/article_cate.html');
        }
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'cate_name' => trim(request('cate_name', '')),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'seq' => (int)request('seq', 99),
            );
            
            $cate_model = new article_cate_model();
            $verifier = $cate_model->verifier($data);
            if(TRUE === $verifier)
            {
                $condition = array('cate_id' => request('id'));
                if($cate_model->update($condition, $data) > 0)
                {
                    $this->clear_cache();
                    $this->prompt('success', '更新文章分类成功', url($this->MOD.'/article_cate', 'index'));
                }   
                $this->prompt('error', '更新文章分类失败');
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new article_cate_model();
            if($this->rs = $cate_model->find(array('cate_id' => (int)request('id', 0))))
            {
                $this->compiler('article/article_cate.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_delete()
    {
        $cate_model = new article_cate_model();
        if($cate_model->delete(array('cate_id' => (int)request('id', 0))) > 0)
        {
            $this->clear_cache();
            $this->prompt('success', '删除文章分类成功', url($this->MOD.'/article_cate', 'index'));
        }   
        $this->prompt('error', '删除文章分类成失败');
    }
    
    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->article_cate_model('indexed_list', null, -1);
    }
}