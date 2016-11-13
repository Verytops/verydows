<?php
class favorite_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $this->compiler('user_favorite_list.html');
    }
}