<?php
class area_controller extends Controller
{
    public function action_children()
    {
        $province = (int)request('province', 0, 'get');
        $city = (int)request('city', 0, 'get');
        $area = new area();
        echo json_encode($area->get_children($province, $city));
    }
}