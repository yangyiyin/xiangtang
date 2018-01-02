<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughSuggestList extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $p = I('p',1);
        $SuggestService = \Common\Service\SuggestService::get_instance();
        $where = [];
        $where['pid'] = 0;
        list($list, $count) = $SuggestService->get_by_where($where, 'id desc', $p);

        $result = [];
        $result['list'] = $this->convert($list);
        $result['has_more'] = has_more($count, $p, \Common\Service\SuggestService::$page_size);

        return result_json(TRUE, '获取成功',$result);
    }

    public function convert($list) {
        if ($list) {
            $ids = result_to_array($list);
            $SuggestService = \Common\Service\SuggestService::get_instance();
            $suggests = $SuggestService->get_by_pids($ids);
            $suggests_map = result_to_map($suggests,'pid');
            foreach ($list as $k => $value) {
                $list[$k]['reply'] = isset($suggests_map[$value['id']]['content']) ? $suggests_map[$value['id']]['content'] : '';
                $list[$k]['content_short'] = $this->short($value['content']);
            }
        }
        return $list;
    }

    private function short($str) {
        $new_str = $str;
        if (mb_strlen($str) > 20) {
            $new_str = mb_substr($str,0,20,'utf-8') . '...';
        }
        return $new_str;
    }
}