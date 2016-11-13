<?php
class cart_controller extends general_controller
{
    public function action_list()
    {
        $cookie = request('CARTS', null, 'cookie');
        if($cookie)
        {
            $cookie = json_decode(stripslashes($cookie), TRUE);
            $goods_model = new goods_model();
            if($cart = $goods_model->get_cart_items($cookie))
            {
                $res = array('status' => 'success', 'cart' => $cart);
            }
            else
            {
                $res = array('status' => 'error');
            }
        }
        else
        {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);
    }
}