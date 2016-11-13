<?php
class adv_position_controller extends general_controller
{
    public function action_index()
    {
        if($id = (int)request('id', 0))
        {
            $position_model = new adv_position_model();
            if($this->position = $position_model->find(array('id' => $id)))
            {
                $adv_model = new adv_model();
                $this->adv_list = $adv_model->find_all(array('position_id' => $id), 'seq ASC');
                $this->type_map = $adv_model->type_map;
                $this->compiler('adv/position_adv.html');
            }
        }
        else
        {
            $position_model = new adv_position_model();
            $this->results = $position_model->find_all(null, 'id DESC', '*', array(request('page', 1), request('pernum', 15)));
            $this->paging = $position_model->page;
            $this->compiler('adv/position_list.html');
        }
    }
    
    public function action_add()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'width' => (int)request('width', 0),
                'height' => (int)request('height', 0),
                'codes' => trim(stripslashes(request('codes', ''))),
            );

            $position_model = new adv_position_model();
            $verifier = $position_model->verifier($data);
            if(TRUE === $verifier)
            {
                $id = $position_model->create($data);
                $position_model->save_tpl_file($id, $data['codes']);
                $this->clear_cache();
                $this->prompt('success', '添加广告位成功', url($this->MOD.'/adv_position', 'index'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $this->compiler('adv/position.html');
        }
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'name' => trim(request('name', '')),
                'width' => (int)request('width', 0),
                'height' => (int)request('height', 0),
                'codes' => trim(stripslashes(request('codes', ''))),
            );
            
            $position_model = new adv_position_model();
            $verifier = $position_model->verifier($data);
            if(TRUE === $verifier)
            {
                $id = request('id');
                if($position_model->update(array('id' => $id), $data) > 0)
                {
                    $position_model->save_tpl_file($id, $data['codes']);
                    $this->clear_cache();
                    $this->prompt('success', '更新广告位成功', url($this->MOD.'/adv_position', 'index'));
                }
                else
                {
                    $this->prompt('error', '更新广告位失败');
                }   
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $position_model = new adv_position_model();
            if($this->rs = $position_model->find(array('id' => (int)request('id', 0))))
            {
                $this->compiler('adv/position.html');
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
        $position_model = new adv_position_model();
        if($position_model->delete(array('id' => $id)) > 0)
        {
            @unlink(APP_DIR.DS.'view'.DS.'adv'.DS.$id.'.html');
            $this->clear_cache();
            $this->prompt('success', '删除广告位成功', url($this->MOD.'/adv_position', 'index'));
        } 
        else
        {
            $this->prompt('error', '删除广告位失败');
        }   
    }
    
    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->adv_position_model('indexed_list', null, -1);
    }
}