<?php
class favorite_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $fav_model = new user_favorite_model();
        $list = $fav_model->get_user_favorites($user_id, array(request('page', 1), request('pernum', 10)));
        if($list)
        {
            $res = array('status' => 'success', 'list' => $list, 'paging' => $fav_model->page);
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
    
    public function action_add()
    {
        $user_id = $this->is_logined();
        $favor_model = new user_favorite_model();
        if($favor_model->add($user_id, (int)request('goods_id', 0)))
        {
            echo json_encode(array('status' => 'success'));
        }
        else
        {
            echo json_encode(array('status' => 'error'));
        }
    }
    
    public function action_delete()
    {
        $user_id = $this->is_logined();
        $id = request('id');
        if($id)
        {
            $fav_model = new user_favorite_model();
            if(is_array($id))
            {
                foreach($id as $v) $fav_model->delete(array('goods_id' => (int)$v, 'user_id' => $user_id));
            }
            else
            {
                $fav_model->delete(array('goods_id' => (int)$id, 'user_id' => $user_id));
            }
            echo json_encode(array('status' => 'success'));
        }
        else
        {
            echo json_encode(array('status' => 'error', 'msg' => '参数非法'));
        }
    }
}