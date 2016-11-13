<?php
class oauth_controller extends general_controller
{
    public function action_index()
    {
        $oauth_model = new oauth_model();
        $this->results = $oauth_model->find_all();
        $this->compiler('oauth/oauth_list.html');
    }

    public function action_edit()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'params' => (array)request('params', array()),
                'enable' => (int)request('enable', 0),
            );

            $oauth_model = new oauth_model();
            $data['params'] = json_encode($data['params']);
            $oauth_model->update(array('party' => request('party')), $data);
            $this->clear_cache();
            $this->prompt('success', '更新成功', url($this->MOD.'/oauth', 'index'));
        }
        else
        {
            $oauth_model = new oauth_model();
            if($rs = $oauth_model->find(array('party' => request('party'))))
            {
                $rs['template'] = 'backend/oauth/'.$rs['party'].'_params.html';
                $rs['params'] = json_decode($rs['params'], TRUE);
                $this->rs = $rs;
                $this->compiler('oauth/oauth.html');
            }
            else
            {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }
    
    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->oauth_model('indexed_list', null, -1);
    }
    
}