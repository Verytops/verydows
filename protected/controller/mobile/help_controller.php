<?php
class help_controller extends general_controller
{
    public function action_index()
    {
        $this->cate_list = vcache::instance()->help_model('cated_help_list');
        $this->compiler('help_index.html');
    }
    
    public function action_list()
    {
        $id = (int)request('id', 0);
        $list = vcache::instance()->help_model('cated_help_list');
        if(isset($list[$id]))
        {
            $this->title = $list[$id]['cate_name'];
            $this->list = $list[$id]['children'];
            
            $this->compiler('help_list.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
    public function action_view()
    {
        $id = (int)request('id', 0);
        $help_model = new help_model();
        if($this->help = $help_model->find(array('id' => $id)))
        {
            $this->compiler('help.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
}