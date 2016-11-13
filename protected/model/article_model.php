<?php
class article_model extends Model
{
    public $table_name = 'article';
    
    public $rules = array
    (
        'title' => array
        (
            'is_required' => array(TRUE, '标题不能为空'),
            'max_length' => array(180, '标题不能超过180个字符'),
        ),
        'brief' => array
        (
            'max_length' => array(240, '简介不能超过240个字符'),
        ),
        'meta_keywords' => array
        (
            'max_length' => array(240, 'META关键词不能超过240个字符'),
        ),
        'meta_description' => array
        (
            'max_length' => array(240, 'META描述不能超过240个字符'),
        ),
    );
    
    public function get_latest_article($limit = 5)
    {
        $sql = "SELECT a.id, a.title, a.picture, a.link, b.cate_id, b.cate_name
                FROM {$this->table_name} AS a
                INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}article_cate AS b
                ON a.cate_id = b.cate_id
                ORDER BY a.created_date DESC
                LIMIT {$limit}
               ";
        return $this->query($sql);
    }
}