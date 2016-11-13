<?php
class favorite_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $favor_model = new user_favorite_model();
        $this->favorites = array
        (
            'list' => $favor_model->get_user_favorites($user_id, array(request('page', 1), 10)),
            'paging' => $favor_model->page,
        );
        $this->compiler('user_favorite_list.html');
    }
    
    public function action_delete()
    {
        $user_id = $this->is_logined();
        $id = request('goods_id', null);
        if(!empty($id))
        {
            $favor_model = new user_favorite_model();
            if(is_array($id))
            {
                foreach($id as $v) $favor_model->delete(array('goods_id' => (int)$v, 'user_id' => $user_id));
            }
            else
            {
                $favor_model->delete(array('goods_id' => (int)$id, 'user_id' => $user_id));
            }
            jump(url('favorite', 'list'));
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}