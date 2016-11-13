<?php
class help_controller extends general_controller
{
    public function action_view()
    {
        $id = (int)request('id', 0);
        $help_model = new help_model();
        if($this->help = $help_model->find(array('id' => $id)))
        {
            $this->cate_help_list = vcache::instance()->help_model('cated_help_list');
            $this->compiler('help.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}