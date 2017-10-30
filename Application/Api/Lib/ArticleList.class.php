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
class ArticleList extends BaseSapi{
    const block_type_news = 'news';
    const block_type_public = 'public';
    const block_type_rules = 'rules';
    const block_type_workinfo = 'workinfo';

    protected $method = parent::API_METHOD_GET;
    private $ArticleService;
    public function init() {
        $this->ArticleService = Service\ArticleService::get_instance();
    }

    public function excute() {
        $p = I('get.p',1);
        $block = I('get.block'); //暂时不做处理

        $where = [];

        if ($block == self::block_type_news) {
            $where['type'] = Model\NfArticleModel::TYPE_NEWS;
        } elseif ($block == self::block_type_public) {
            $where['type'] = Model\NfArticleModel::TYPE_PUBLIC;
        } elseif ($block == self::block_type_rules) {
            $where['type'] = Model\NfArticleModel::TYPE_RULES;
        } elseif ($block == self::block_type_workinfo) {
            $where['type'] = ['in', [Model\NfArticleModel::TYPE_WORKINFO,Model\NfArticleModel::TYPE_WORKAPPLY]];
        } else {
            $where['type'] = 0;
        }


        if ($this->from == self::FROM_SERVICE) {
            $where['platform'] = ['in', [self::FROM_SERVICE, self::FROM_ALL]];
        } elseif($this->from == self::FROM_RETAIL) {
            $where['platform'] =  ['in', [self::FROM_RETAIL, self::FROM_ALL]];
        } else {
            $where['platform'] =  ['eq', self::FROM_ALL];
        }
        $where['status'] = \Common\Model\NfArticleModel::STATUS_NORMAL;
        list($list, $count) = $this->ArticleService->get_by_where($where, 'id desc', $p);
        $result = new \stdClass();
        $result->list = $this->convert_data($list);
        $result->has_more = has_more($count, $p, Service\ArticleService::$page_size);
        return result_json(TRUE, '', $result);
    }

    private function convert_data($list) {
        $data = [];
        if ($list) {
            foreach ($list as $_item) {
                $_item['id'] = (int) $_item['id'];
                $tmp = [];
                $tmp = convert_obj($_item, 'id,title,create_time=date');
                if ($tmp == \Common\Model\NfArticleModel::TYPE_WORKINFO) {
                    $tmp->help_cat = 1;
                    $tmp->help_cat_desc = '招聘';
                }
                if ($tmp == \Common\Model\NfArticleModel::TYPE_WORKAPPLY) {
                    $tmp->help_cat = 2;
                    $tmp->help_cat_desc = '求职';
                }
                $data[] = $tmp;
            }
        }
        return $data;
    }

}