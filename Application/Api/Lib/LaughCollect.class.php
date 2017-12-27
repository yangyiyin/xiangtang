<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class LaughCollect extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $aid = $this->post_data['aid'];
        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();

        $has_one = $ArticleEventsService->get_by_aid_uid_type($aid,$this->uid,\Common\Model\NfArticleEventsModel::TYPE_COLLECT);

        if ($has_one) {
            return result_json(TRUE, '您已收藏');
        }

        $data = [];
        $data['type'] = \Common\Model\NfArticleEventsModel::TYPE_COLLECT;
        $data['desc'] = '收藏';
        $data['uid'] = $this->uid;
        $data['aid'] = $aid;

        $ret = $ArticleEventsService->add_one($data);

        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        //增加文章点击数
        $ArticleCliksService = \Common\Service\ArticleClicksService::get_instance();
        $info = $ArticleCliksService->get_info_by_aid($aid, \Common\Model\NfArticleClicksModel::TYPE_COLLECT);
        if ($info) {
            $data = [];
            $data['count'] = $info['count'] + 1;
            $ArticleCliksService->update_by_id($info['id'],$data);
        } else {
            $data = [];
            $data['type'] = \Common\Model\NfArticleClicksModel::TYPE_COLLECT;
            $data['desc'] = '收藏';
            $data['count'] = 1;
            $data['aid'] = $aid;
            $ArticleCliksService->add_one($data);
        }


        return result_json(TRUE, '收藏成功');
    }

}