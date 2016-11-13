<?php
class article_controller extends general_controller
{
    public function action_index()
    {
        $this->cate_list = vcache::instance()->article_cate_model('indexed_list');
        $this->compiler('article_list.html');
    }
    
    public function action_view()
    {
        $id = (int)request('id', 0);
        $article_model = new article_model();
        if($article = $article_model->find(array('id' => $id)))
        {
            if(!empty($article['link'])) jump($article['link']);
            $this->article = $article;
            $this->article_cate_list = vcache::instance()->article_cate_model('indexed_list');
            $this->compiler('article.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }
}