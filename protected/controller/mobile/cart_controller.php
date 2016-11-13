<?php
class cart_controller extends general_controller
{
    public function action_index()
    {
        $this->compiler('cart.html');
    }

}