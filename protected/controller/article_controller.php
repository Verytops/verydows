<?php
class article_controller extends general_controller
{
    public function action_index()
    {
        $article_model = new article_model();
        $this->article_list = $article_model->find_all(null, 'created_date DESC', 'id, title, link, created_date', array(request('page', 1), 20));
        $this->article_paging = $article_model->page;
        $this->article_cate_list = vcache::instance()->article_cate_model('indexed_list');
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
            jump(url('main', '404'));
        }
    }
    
    public function action_category()
    {
        $cate_id = (int)request('id', 0);
        $article_cate_model = new article_cate_model();
        if($this->cate = $article_cate_model->find(array('cate_id' => $cate_id)))
        {
            $article_model = new article_model();
            $this->article_list = $article_model->find_all(array('cate_id' => $cate_id), 'created_date DESC', 'id, title, link, created_date', array(request('page', 1), 20));
            $this->article_paging = $article_model->page;
            $this->article_cate_list = vcache::instance()->article_cate_model('indexed_list');
            $this->compiler('article_category.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}