<?php
class user_account_model extends Model
{
    public $table_name = 'user_account';
    
    public $rules = array
    (
        'balance' => array
        (
            'is_decimal' => array(TRUE, '余额值格式不正确'),
        ),
        'points' => array
        (
            'is_nonegint' => array(TRUE, '积分值格式不正确'),
        ),
        'exp' => array
        (
            'is_nonegint' => array(TRUE, '经验值格式不正确'),
        ),
    );
    
    public function get_user_account($user_id)
    {
        $sql = "SELECT balance, points, exp, group_name, discount_rate 
                FROM {$this->table_name} JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}user_group
                WHERE user_id = :user_id AND exp >= min_exp ORDER BY min_exp DESC LIMIT 1";
        if($res = $this->query($sql, array(':user_id' => $user_id))) return $res[0];
        return null;
    }
    
    public function update_account($user_id, $data)
    {
        $sql = "UPDATE {$this->table_name}
                SET
                    balance = balance + :balance,
                    points = points + :points,
                    exp = exp + :exp
                WHERE
                    user_id = :user_id
               ";
               
        $binds = array(':balance' => $data['balance'], ':points' => $data['points'], ':exp' => $data['exp'], ':user_id' => $user_id);
        if($this->execute($sql, $binds) > 0) return TRUE;
        return FALSE;
    }
}