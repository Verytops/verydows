<?php
class email_queue_controller extends general_controller
{
    public function action_index()
    {
        $queue_model = new email_queue_model();
        $this->results = $queue_model->find_all(null, 'id DESC', '*', array(request('page', 1), 15));
        $this->paging = $queue_model->page;
        $this->compiler('email/queue_list.html');
    }
    
    public function action_send()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $queue_model = new email_queue_model();
            foreach($id as $v) $affected += $queue_model->send($v) ? 1 : 0;
            $failure = count($id) - $affected;
            $this->prompt('default', "成功发送 {$affected} 个邮件, 失败 {$failure} 个", url($this->MOD.'/email_queue', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }

    public function action_delete()
    {
        $id = request('id');
        if(!empty($id) && is_array($id))
        {
            $affected = 0;
            $queue_model = new email_queue_model();
            foreach($id as $v) $affected += $queue_model->delete(array('id' => (int)$v));
            $failure = count($id) - $affected;
            $this->prompt('default', "成功删除 {$affected} 个记录, 失败 {$failure} 个", url($this->MOD.'/email_queue', 'index'));
        }
        else
        {
            $this->prompt('error', '参数错误');
        }
    }
}