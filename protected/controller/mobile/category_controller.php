<?php
class category_controller extends general_controller
{
    public function action_index()
    {
        $cate_model = new goods_cate_model();
        $this->cate_list = $cate_model->goods_cate_bar();
        $this->compiler('category.html');
    }

}