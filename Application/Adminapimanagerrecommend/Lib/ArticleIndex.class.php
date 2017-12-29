<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;

class ArticleIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {
        $p = I('p',1);
        $content = I('title');//字段含义问题
        $type = I('doc_type');
        $service = Service\ArticleService::get_instance();

        $map = \Common\Model\NfArticleModel::$status_from_map;

        $type_ids = array_values($map);
        $where=[];
        //$where['from'] = $type_ids[0];
        if ($content) {
            $where['content'] = ['like', '%' . $content . '%'];
        }
        if ($type) {
            if ((isset($map[$type]) && $map[$type])) {
                $where['from'] = $map[$type];
            }
        }
        $where['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
        list($list, $count) = $service->get_by_where($where, 'id desc', $p);

        $list = $this->convert($list);
        $type = $type ? $type : array_keys($map)[0];
        return result_json(TRUE, '', ['list'=>$list,'total'=>$count,'type'=>$type,'types'=>array_keys($map)]);

    }

    protected function convert($list) {

        $uids = result_to_array($list, 'uid');
        $UserService = \Common\Service\UserService::get_instance();
        $users = $UserService->get_by_ids($uids);
        $users_map = result_to_map($users, 'id');
        $status_map = \Common\Model\NfArticleModel::$status_map;
        if ($list) {
            foreach ($list as $key => $_li) {
                $list[$key]['status_desc'] = isset($status_map[$_li['status']]) ? $status_map[$_li['status']] : '未知状态';

                $list[$key]['user'] = [];
                if ($_li['from'] == \Common\Model\NfArticleModel::FROM_CUSTOM) {

                    if (isset($users_map[$_li['uid']])) {

                        $list[$key]['user'] =  $users_map[$_li['uid']] ;
                    }

                } elseif ($_li['from'] == \Common\Model\NfArticleModel::FROM_ADMIN) {
                    $list[$key]['user'] = [
                        'user_name' => '【后台发布】',
                    ];
                } else {

                }


            }

        }
        return $list;
    }

}