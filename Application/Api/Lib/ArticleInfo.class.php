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
class ArticleInfo extends BaseApi{
    const block_type_about = 'about';
    const block_type_contact = 'contact';
    protected $method = parent::API_METHOD_GET;
    private $ArticleService;
    public function init() {
        $this->ArticleService = Service\ArticleService::get_instance();
    }

    public function excute() {
        $id = I('get.id');
        $block = I('get.block'); //暂时不做处理
        if (!$id && !$block) {
            result_json(FALSE, '缺少参数');
        }
        $result = new \stdClass();
        $result->content = '';
        if ($id) {
            $info = $this->ArticleService->get_info_by_id($id);
            list($pre, $next) = $this->ArticleService->get_pre_next_by_id($id);
            $result->pre = convert_obj($pre, 'id,title');
            $result->next = convert_obj($next, 'id,title');
        } elseif ($block == self::block_type_about) {
            $info = $this->ArticleService->get_about();
        } elseif ($block == self::block_type_contact) {
            $info = $this->ArticleService->get_contact();
        }

        if ($info) {
            $result->content = $info['content'];
        }

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