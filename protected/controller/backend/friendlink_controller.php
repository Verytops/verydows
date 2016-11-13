<?php
class friendlink_controller extends general_controller
{
    public function action_index()
    {
        $link_model = new friendlink_model();
        $this->results = $link_model->find_all(null, 'id DESC', '*', array(request('page', 1), request('pernum', 15)));
        $this->paging = $link_model->page;
        $this->compiler('operation/friendlink_list.html');
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'url' => trim(request('url', '')),
                'seq' => (int)request('seq', 99),
            );
            
            $link_model = new friendlink_model();
            $verifier = $link_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($_FILES['logo_file']['name']))
                {
                    $save_path = 'upload/friendlink';
                    $uploader = new uploader($save_path);
                    $logo = $uploader->upload_file('logo_file');
                    if($logo['error'] != 'success') $this->prompt('error', $logo['error']);
                    $data['logo'] = $logo['url'];
                }
                else
                {
                    $data['logo'] = trim(request('logo_src', ''));
                }
                
                $link_model->create($data);
                $this->prompt('success', '添加友情链接成功', url($this->MOD.'/friendlink', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('operation/friendlink.html');
        }
    }
    
    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'url' => trim(request('url', '')),
                'seq' => (int)request('seq', 99),
            );
            
            $link_model = new friendlink_model();
            $verifier = $link_model->verifier($data);
            if(TRUE === $verifier)
            {
                if(!empty($_FILES['logo_file']['name']))
                {
                    $save_path = 'upload/friendlink';
                    $uploader = new uploader($save_path);
                    $logo = $uploader->upload_file('logo_file');
                    if($logo['error'] != 'success') $this->prompt('error', $logo['error']);
                    $data['logo'] = $logo['url'];
                }
                else
                {
                    $data['logo'] = trim(request('logo_src', ''));
                }
                $link_model->update(array('id' => (int)request('id', 0)), $data);
                $this->prompt('success', '更新友情链接成功', url($this->MOD.'/friendlink', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $link_model = new friendlink_model();
            if($this->rs = $link_model->find(array('id' => (int)request('id', 0))))
            {
                $this->compiler('operation/friendlink.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_delete()
    {
        $id = request('id');
        if(is_array($id) && !empty($id))
        {
            $link_model = new friendlink_model();
            $affected = 0;
            foreach($id as $v) $affected += $link_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个记录, 失败 {$failure} 个", url($this->MOD.'/friendlink', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
}