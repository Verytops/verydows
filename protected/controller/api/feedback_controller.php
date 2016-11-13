<?php
class feedback_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $feedback_model = new feedback_model();
        $list = $feedback_model->get_user_feedback($user_id, array(request('page', 1), request('pernum', 10)));
        if($list)
        {
            $res = array('status' => 'success', 'list' => $list, 'paging' => $feedback_model->page);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
    
    public function action_save()
    {
        $user_id = $this->is_logined();
        $data = array
        (
            'user_id' => $user_id,
            'type' => (int)request('type', 0, 'post'),
            'subject' => strip_tags(trim(request('subject', '', 'post'))),
            'content' => strip_tags(trim(request('content', '', 'post'))),
            'mobile' => trim(request('mobile', '', 'post')),
            'created_date' => $_SERVER['REQUEST_TIME'],
        );
        
        $feedback_model = new feedback_model();
        $verifier = $feedback_model->verifier($data);
        if(TRUE === $verifier)
        {
            if($feedback_model->create($data))
            {
                $res = array('status' => 'success');
            }
            else
            {
                $res = array('status' => 'error', '保存失败,请稍后重试');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => $verifier[0]);
        }
        echo json_encode($res);
    }
    
    public function action_messaging()
    {
        $user_id = $this->is_logined();
        $fb_id = (int)request('id', 0);
        $feedback_model = new feedback_model();
        if(!$feedback_model->find(array('fb_id' => $fb_id, 'user_id' => $user_id, 'status' => 1)))
        {
            die(json_encode(array('status' => 'error', 'msg' => '目前无法发送消息')));
        }
        
        $data = array
        (
            'fb_id' => $fb_id,
            'content' => strip_tags(trim(request('content', '', 'post'))),
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
        $message_model = new feedback_message_model();
        $verifier = $message_model->verifier($data);
        if(TRUE === $verifier)
        {
            if($message_model->create($data))
            {
                $res = array('status' => 'success', 'dateline' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']));
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '发送失败,请稍后重试');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => $verifier[0]);
        }
        echo json_encode($res);
    }
}