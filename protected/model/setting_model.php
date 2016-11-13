<?php
class setting_model extends Model
{
    public $table_name = 'setting';
    
    /**
     * 获取全部设置
     */
    public function get_config()
    {
        $find_all = $this->find_all();
        $config = array_column($find_all, 'sv', 'sk');
        $config['rewrite_rule'] = json_decode($config['rewrite_rule'], TRUE);
        $config['http_host'] = baseurl();
        $config['goods_img_thumb'] = json_decode($config['goods_img_thumb'], TRUE);
        $config['goods_album_thumb'] = json_decode($config['goods_album_thumb'], TRUE);
        if($config['upload_goods_filetype']) $config['upload_goods_filetype'] = explode('|', $config['upload_goods_filetype']);
        $config['upload_goods_filesize'] = size_to_bytes($config['upload_goods_filesize']);
        return $config;
    }
    
    /**
     * 更新设置
     */
    public function update_config()
    {
        $config = $this->get_config();
        $codes = "<?php \nreturn ".var_export($config, TRUE).";";
        return file_put_contents(APP_DIR.DS.'protected'.DS.'cache'.DS.'setting.php', $codes);
    }
    
    /**
     * 获取数据库版本
     */
    public function get_db_version()
    {
        $sth = $this->db_instance($GLOBALS['mysql'], 'master')->prepare('SELECT VERSION()');
        $sth->execute();
        return $sth->fetchColumn();
    }
    
    /**
     * 获取数据库大小
     */
    public function get_db_size()
    {   
        $sql = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size
                FROM information_schema.TABLES WHERE table_schema = '{$GLOBALS['mysql']['MYSQL_DB']}'
                GROUP BY table_schema";
        $size = $this->query($sql);
        return round($size[0]['size'], 2) . ' MB';
    }
    
    /**
     * 获取上传目录大小
     */
    public static function get_upload_size()
    {
        $dir = APP_DIR.DS.'upload';
        $size = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) $size += $file->getSize();
        return bytes_to_size($size);
    }
}
