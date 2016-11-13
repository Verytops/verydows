<?php
class goods_controller extends general_controller
{
    public function action_index()
    {
        $id = (int)request('id', 0);
        $condition = array('goods_id' => $id);
        $goods_model = new goods_model();
        if($goods = $goods_model->find($condition))
        {
            $cate_model = new goods_cate_model();
            $this->breadcrumbs = $cate_model->breadcrumbs($goods['cate_id']);
            //商品信息
            $this->goods = $goods;
            //商品相册
            $album_model = new goods_album_model();
            $this->album_list = $album_model->find_all($condition);
            //商品评分
            $review_model = new goods_review_model();
            $this->rating = $review_model->get_rating_stats($id);
            //购买选择项
            $optl_model = new goods_optional_model();
            $this->opt_list = $optl_model->get_goods_optional($id);
            //商品规格
            $attr_model = new goods_attr_model();
            $this->specs = $attr_model->get_goods_specs($id);
            //关联商品
            $this->related = $goods_model->get_related($id, $GLOBALS['cfg']['goods_related_num']);
            //热销商品
            $this->bestseller = vcache::instance()->goods_model('get_bestseller', null, 10, $GLOBALS['cfg']['data_cache_lifetime']);
            //保存浏览历史
            $goods_model->set_history($id);

            $this->compiler('goods.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}