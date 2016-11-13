<?php
class help_controller extends general_controller
{
    public function action_index()
    {
        $cate_model = new help_cate_model();
        $this->cate_list = $cate_model->indexed_list();
        $help_model = new help_model();
        $this->results = $help_model->find_all(null, 'id ASC', 'id, cate_id, title, seq', array(request('page', 1), 15));
        $this->paging = $help_model->page;
        $this->compiler('article/help_list.html');
    }

    public function action_add()
    {
        $step = request('step');
        if(request('step') == 'submit')
        {
            $data = array
            (
                'title' => trim(request('title', '')),
                'cate_id' => (int)request('cate_id', 0),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'link' => trim(request('link', '')),
                'seq' => (int)request('seq', 99),
                'content' => trim(stripslashes(request('content', ''))),
            );
                
            $help_model = new help_model();
            $verifier = $help_model->verifier($data);
            if(TRUE === $verifier)
            {     
                $help_model->create($data);
                $this->clear_cache();
                $this->prompt('success', '添加帮助成功', url($this->MOD.'/help', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $cate_model = new help_cate_model();
            $this->cate_list = $cate_model->find_all(null, 'cate_id ASC');
            $this->compiler('article/help.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'title' => trim(request('title', '')),
                'cate_id' => (int)request('cate_id', 0),
                'meta_keywords' => trim(request('meta_keywords', '')),
                'meta_description' => trim(request('meta_description', '')),
                'link' => trim(request('link', '')),
                'seq' => (int)request('seq', 99),
                'content' => trim(stripslashes(request('content', ''))),
            );
            
            $help_model = new help_model();
            $verifier = $help_model->verifier($data);
            if(TRUE === $verifier)
            { 
                if($help_model->update(array('id' => (int)request('id', 0)), $data) > 0)
                {
                    $this->clear_cache();
                    $this->prompt('success', '更新帮助成功', url($this->MOD.'/help', 'index'));
                }  
                else
                {
                    $this->prompt('error', '更新帮助失败');
                }     
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $help_model = new help_model();
            if($this->rs = $help_model->find(array('id' => (int)request('id', 0))))
            {
                $cate_model = new help_cate_model();
                $this->cate_list = $cate_model->find_all(null, 'cate_id ASC');
                $this->compiler('article/help.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    public function action_editor()
    {
        $uploader = new uploader('upload/help/editor');
        $file = $uploader->upload_file('upfile');
        if($file['error'] == 'success')
        {
            $callback = request('callback');
            $res = array('state' => 'SUCCESS', 'url' => $file['url']);
            if($callback) echo '<script>'.$callback.'('.json_encode($res).')</script>';
            echo json_encode($res);
        }
        else
        {
            echo "<script>alert('{$file['error']}')</script>";
        }
    }
    
    public function action_delete()
    {
        $id = request('id');
        if(is_array($id) && !empty($id))
        {
            $affected = 0;
            $help_model = new help_model();
            foreach($id as $v) $affected += $help_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->clear_cache();
            $this->prompt('default', "成功删除 {$affected} 个帮助记录, 失败 {$failure} 个", url($this->MOD.'/help', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
    
    //清除缓存
    private function clear_cache()
    {
        vcache::instance()->help_model('cated_help_list', null, -1);
    }
}