<?php
class visitor_stats_model extends Model
{
    public $table_name = 'visitor_stats';
    
    public $browser_map = array('Unknown', 'IE', 'Chrome', 'Firefox', 'Safari', 'Opera');
    
    public $platform_map = array('Unknown', 'Windows', 'Mac', 'Linux');
    
    public function do_stats($data)
    {
        $data = $this->_filter($data);
        $today = strtotime('today');
        $row = array('sessid' => $data['sessid'], 'ip' => $data['ip'], 'dateline' => $today);
        if(!$this->find($row))
        {
            $data['dateline'] = $today;
            $this->create($data);
        }
        else
        {
            $this->incr($row, 'pv', 1);
        }
    }
    
    public function stats_period_traffic($start_date, $end_date, $sort = 'dateline ASC')
    {
        $sql = "SELECT dateline, SUM(pv) as pv, COUNT(distinct(ip)) as ip, COUNT(distinct(sessid)) as uv
                FROM {$this->table_name}
                WHERE dateline >= :start_date AND dateline <= :end_date
                GROUP BY dateline
                ORDER BY {$sort}
               ";
        return $this->query($sql, array(':start_date' => $start_date, ':end_date' => $end_date));
    }
    
    public function stats_period_referrer($type, $start_date, $end_date)
    {
        $where = " WHERE dateline >= :start_date AND dateline <= :end_date";
        $binds = array(':start_date' => $start_date, ':end_date' => $end_date);
        if($type == 'site')
        {
            $sql = "SELECT COUNT(*) AS visits, referrer FROM {$this->table_name}
                    {$where}
                    GROUP BY referrer
                    ORDER BY visits DESC
                    LIMIT 6
                   ";
            $results = $this->query($sql, $binds);
            if(isset($results[6]))
            {
                unset($results[6]);
                $sql = "SELECT COUNT(*) AS qty FROM {$this->table_name} {$where}";
                $total = $this->query($sql, $binds);
                $sum = 0;
                foreach($results as $v) $sum += $v['visits'];
                $results[6] = array('referrer' => '其他网站', 'visits' => $total[0]['qty'] - $sum);
            }
            return $results;
        }
        else
        {
            $results = array();
            $sql = "SELECT COUNT(*) AS visits, referrer
                    FROM {$this->table_name}
                    {$where}
                    GROUP BY referrer
                   ";
            
            if($rows = $this->query($sql, $binds))
            {
                $results = array
                (
                    'engine' => array('referrer' => '搜索引擎', 'visits' => 0),
                    'direct' => array('referrer' => '直接输入网址或书签', 'visits' => 0),
                    'self' => array('referrer' => '站内链接', 'visits' => 0),
                    'external' => array('referrer' => '外部链接', 'visits' => 0),
                );
                foreach($rows as $v)
                {
                    if(preg_match('/([0-9a-z_-]\.|^)(baidu|sogou|haosou|bing|youdao|google)(\.com)/i', $v['referrer']) > 0) $results['engine']['visits'] += $v['visits'];
                    elseif(empty($v['referrer'])) $results['direct']['visits'] += $v['visits'];
                    elseif(stripos($v['referrer'], $_SERVER['SERVER_NAME']) !== FALSE) $results['self']['visits'] += $v['visits'];
                    else $results['external']['visits'] += $v['visits'];
                }
            }
            return $results;
        }
    }
    
    public function stats_period_terminal($type, $start_date, $end_date)
    {
        $where = " WHERE dateline >= :start_date AND dateline <= :end_date";
        $binds = array(':start_date' => $start_date, ':end_date' => $end_date);
        $sql = "SELECT COUNT(*) AS visits, {$type}
                FROM {$this->table_name}
                {$where}
                GROUP BY {$type}
               ";
        if($results = $this->query($sql, $binds))
        {
            if($type == 'browser') $map = $this->browser_map; else $map = $this->platform_map;
            foreach($results as $k => $v) $results[$k][$type] = $map[$v[$type]];
        }
        return $results;
    }
    
    public function stats_period_area($start_date, $end_date)
    {
        $where = " WHERE dateline >= :start_date AND dateline <= :end_date";
        $binds = array(':start_date' => $start_date, ':end_date' => $end_date);
        $sql = "SELECT COUNT(*) AS visits, area FROM {$this->table_name} {$where} GROUP BY area";
        return $this->query($sql, $binds);
    }
    
    private function _filter($data)
    {
        if(empty($data['sessid']))
        {
             $data['sessid'] = md5(uniqid(session_id(), TRUE));
             setcookie('UVID', $data['sessid'], strtotime('today') + 86400, '/');
        }
        if(!preg_match('/^(?=\d+\.\d+\.\d+\.\d+$)(?:(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])\.?){4}$/', $data['ip'])) $data['ip'] = get_ip();
        if(empty($data['referrer'])) $data['referrer'] = $this->_get_referer();
        if(!isset($this->platform_map[$data['platform']])) $data['platform'] = $this->_get_platform();
        if(!isset($this->platform_map[$data['browser']])) $data['browser'] = $this->_get_browser();
        return $data;
    }
    
    private function _get_browser()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if((preg_match('/MSIE/i', $agent) && !preg_match('/Opera/i',$agent)) || preg_match('/Trident/i', $agent)) $browser = 1;
        elseif(preg_match('/Chrome/i', $agent)) $browser = 2; 
        elseif(preg_match('/Firefox/i', $agent)) $browser = 3; 
        elseif(preg_match('/Safari/i', $agent)) $browser = 4;
        elseif(preg_match('/Opera/i', $agent)) $browser = 5;
        else $browser = 0;
        return $browser;
    }
    
    private function _get_platform()
    { 
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/windows|win32/i', $agent)) $platform = 1;
        elseif(preg_match('/macintosh|mac os x/i', $agent)) $platform = 2;
        elseif(preg_match('/linux/i', $agent)) $platform = 3;
        else $platform = 0;
        return $platform;
    }
    
    private function _get_referer()
    {
        if(isset($_SERVER['HTTP_REFERER']))
        {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            if(isset($url['host'])) return $url['host'];
        }
        return '';
    }
}
