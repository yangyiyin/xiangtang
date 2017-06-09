<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class ArticleList extends BaseApi{
    const block_type_news = 'news';
    protected $method = parent::API_METHOD_GET;
    private $ArticleService;
    public function init() {
        $this->ArticleService = Service\ArticleService::get_instance();
    }

    public function excute() {
        $p = I('get.p');
        $block = I('get.block'); //暂时不做处理

        $where = [];

        if ($block == self::block_type_news) {
            $where['type'] = Model\NfArticleModel::TYPE_NEWS;
        }
        list($list, $count) = $this->ArticleService->get_by_where($where, 'id desc', $p);
        $result = new \stdClass();
        $result->list = $this->convert_data($list);

        return result_json(TRUE, '', $result);
    }

    private function convert_data($list) {
        $data = [];
        if ($list) {
            foreach ($list as $_item) {
                $_item['id'] = (int) $_item['id'];
                $tmp = [];
                $tmp = convert_obj($_item, 'id,title,create_time=date');
                $data[] = $tmp;
            }
        }
        return $data;
    }

}