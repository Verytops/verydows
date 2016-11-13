<?php
class goods_controller extends general_controller
{
    public function action_index()
    {
        $condition = array('goods_id' => (int)request('id', 0));
        $goods_model = new goods_model();
        if($goods = $goods_model->find($condition))
        {
            //商品信息
            $this->goods = $goods;
            //商品相册
            $album_model = new goods_album_model();
            $this->album_list = $album_model->find_all($condition);
            //购买选择项
            $optl_model = new goods_optional_model();
            $this->opt_list = $optl_model->get_goods_optional($condition['goods_id']);
            //商品评价
            $review_model = new goods_review_model();
            $this->review_rating = $review_model->get_rating_stats($condition['goods_id']);
            //关联商品
            $this->related = $goods_model->get_related($condition['goods_id'], $GLOBALS['cfg']['goods_related_num']);
            //热销商品
            $this->bestseller = vcache::instance()->goods_model('get_bestseller', null, 10, $GLOBALS['cfg']['data_cache_lifetime']);     
            //保存浏览历史
            $goods_model->set_history($condition['goods_id']);
            
            $this->compiler('goods.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
        
    public function action_illustrated()
    {
        $goods_model = new goods_model();
        if($this->goods = $goods_model->find(array('goods_id' => (int)request('id', 0)), null, 'goods_name, goods_content'))
        {
            $this->compiler('goods_illustrated.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
    
    public function action_specs()
    {
        $attr_model = new goods_attr_model();
        $this->specs = $attr_model->get_goods_specs((int)request('id', 0));
        $this->compiler('goods_specs.html');
    }
    
    public function action_reviews()
    {
        $id = (int)request('id', 0);
        $goods_model = new goods_model();
        if($this->goods = $goods_model->find(array('goods_id' => $id), null, 'goods_id, goods_name'))
        {
            $review_model = new goods_review_model();  
            $this->rating = $review_model->get_rating_stats($id);
            $this->compiler('goods_review_list.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }

}