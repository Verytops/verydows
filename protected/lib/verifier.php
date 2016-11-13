<?php
class verifier
{
    private $data, $rules;
    
    public function __construct($data, $rules)
    {	
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public function rules_slices($slices = array())
    {
        if(!empty($slices))
        {
            $rules = $this->rules;
            foreach ($slices as $k => $v)
            {
                if($v === FALSE) unset($rules[$k]);
                else foreach($v as $vv) unset($rules[$k][$vv]);
            }
            $this->rules = $rules;
        }
    }
    
    public function checking()
    {
        $err = array();
        foreach($this->rules as $k => $v)
        {
            if(isset($this->data[$k]))
            {
                foreach($v as $kk => $vv)
                {
                    if(method_exists($this, $kk))
                    {
                        if(FALSE == $this->$kk($this->data[$k], $vv[0])) array_push($err, $vv[1]); 
                    }
                    else
                    {
                        if(is_bool($vv[0]) && isset($vv[1]))
                        {
                            if(FALSE == $vv[0]) array_push($err, $vv[1]);
                        }
                        else
                        {
                            array_push($err, "未定义的验证规则：{$kk}");
                        }
                    }
                }
            }
            else
            {
                array_push($err, "未找到对应验证数据：{$k}");
            }
        }
        
        if(empty($err)) return TRUE;
        return $err;
    }
    
    /*----------------------------------------- */
    /* 检查数据是否非空
    /*----------------------------------------- */
    public static function is_required($val, $right)
    {
        if(is_array($val)) return (count($val) > 0) === $right;
        return (strlen($val) > 0) === $right;
    }
    /*----------------------------------------- */
    /* 数据最小字符长度
    /*----------------------------------------- */
    public static function min_length($val, $right)
    {
        return mb_strlen($val, 'utf-8') >= $right;
    }
    /*----------------------------------------- */
    /* 数据最大字符长度
    /*----------------------------------------- */
    public static function max_length($val, $right)
    {
        return mb_strlen($val, 'utf-8') <= $right;
    }
    /*----------------------------------------- */
    /* 数据是否是email格式
    /*----------------------------------------- */
    public static function is_email($val, $right)
    {
        return (preg_match('/^$|^[a-zA-Z0-9]+([._\-\+]*[a-zA-Z0-9]+)*@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]+$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否等于给定的值
    /*----------------------------------------- */
    public function equal_to($val, $right)
    {
        return $val == $this->data[$right];
    }
    /*----------------------------------------- */
    /* 数据是否是手机格式
    /*----------------------------------------- */
    public static function is_moblie_no($val, $right)
    {
        return (preg_match('/^$|^1[3|4|5|7|8]\d{9}$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是邮政编码
    /*----------------------------------------- */
    public static function is_zip($val, $right)
    {
        return (preg_match('/^$|^[0-9]{6}$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是非负整数
    /*----------------------------------------- */
    public static function is_nonegint($val, $right)
    {
        return (preg_match('/^$|^(0|\+?[1-9][0-9]*)$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是decimal(10进位长度为2)的格式
    /*----------------------------------------- */
    public static function is_decimal($val, $right)
    {
        return (preg_match('/^$|^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是数字
    /*----------------------------------------- */
    public static function is_digit($val, $right)
    {
        return (preg_match('/^$|^[0-9]*$/', $val) != 0) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是有效的时间格式
    /*----------------------------------------- */
    public static function is_time($val, $right)
    {
        return (strtotime($val) !== FALSE || empty($val)) == $right;
    }
    /*----------------------------------------- */
    /* 数据是否是0~99之间整数
    /*----------------------------------------- */
    public static function is_seq($val, $right)
    {
        return (preg_match('/^$|^([1-9]\d|\d)$/', $val) != 0) == $right;
    }
}
?>