<?php
class aftersales_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $aftersales_model = new aftersales_model();
        $list = $aftersales_model->get_user_aftersales($user_id, array(request('page', 1), request('pernum', 10)));
        if($list)
        {
            $res = array('status' => 'success', 'list' => $list, 'paging' => $aftersales_model->page);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
    
    public function action_apply()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $goods_id = (int)request('goods_id', 0);
        $aftersales_model = new aftersales_model();
        if($aftersales_model->find(array('user_id' => $user_id, 'order_id' => $order_id, 'goods_id' => $goods_id)))
        {
            die(json_encode(array('status' => 'error', '已申请过了售后')));
        }
        
        $data = array
        (
            'user_id' => $user_id,
            'order_id' => $order_id,
            'goods_id' => $goods_id,
            'goods_qty' => (int)request('goods_qty', 1),
            'type' => (int)request('type', 0),
            'cause' => trim(strip_tags(request('cause', ''))),
            'mobile' => trim(request('mobile', '')),
            'created_date' => $_SERVER['REQUEST_TIME'],
            'status' => 0,
        );
        
        if($aftersales_model->check_apply_allowed($user_id, $data['order_id'], $data['goods_id'], $data['goods_qty']))
        {
            $verifier = $aftersales_model->verifier($data);
            if(TRUE === $verifier)
            {
                if($aftersales_model->create($data))
                {
                    $res = array('status' => 'success');
                }
                else
                {
                    $res = array('status' => 'error', '提交失败,请稍后重试');
                }
            }
            else
            {
                $res = array('status' => 'error', 'msg' => $verifier[0]);
            }
        }
        else
        {
            $res = array('status' => 'error', '不符合申请售后要求');
        }
        echo json_encode($res);
    }
    
    public function action_messaging()
    {
        $user_id = $this->is_logined();
        $as_id = (int)request('id', 0);
        $aftersales_model = new aftersales_model();
        if(!$aftersales_model->find(array('as_id' => $as_id, 'user_id' => $user_id, 'status' => 1)))
        {
            die(json_encode(array('status' => 'error', 'msg' => '目前无法发送消息')));
        }
        
        $data = array
        (
            'as_id' => $as_id,
            'content' => strip_tags(trim(request('content', '', 'post'))),
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
        $message_model = new aftersales_message_model();
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