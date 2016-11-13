<?php
class stats_controller extends Controller
{
    public function action_count()
    {
        if(!empty($GLOBALS['cfg']['visitor_stats']))
        {
            $data = array
            (
                'sessid' => request('UVID', '', 'cookie'),
                'ip' => request('ip', '', 'post'),
                'referrer' => request('referrer', '', 'post'), 
                'platform' => request('platform', 0, 'post'),
                'browser' => request('browser', 0, 'post'),
                'area' => request('area', '', 'post'),
            );
            
            $stats_model = new visitor_stats_model();
            $stats_model->do_stats($data);
        }
    }
}