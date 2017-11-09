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
class UserInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $UserService;
    public function init() {
        $this->UserService = Service\UserService::get_instance();
    }

    public function excute() {
        $info = $this->UserService->get_info_by_id($this->uid);
        $data = convert_obj($info, 'user_name,avatar');
        //$data->type = (int) $data->type;
        $data->avatar = item_img(get_cover(46, 'path'));

        //获取积分
        $AccountService = \Common\Service\AccountService::get_instance();
        $info = $AccountService->get_info_by_uid($this->uid);
        $data->count = isset($info['sum']) ? $info['sum'] : 0;

        //获取点赞数
        $ArticleEventsService = \Common\Service\ArticleEventsService::get_instance();
        $likes = $ArticleEventsService->get_by_uid_type($this->uid, \Common\Model\NfArticleEventsModel::TYPE_LIKE);
        $data->likes = $likes ? count($likes) : 0;
        return result_json(TRUE, '', $data);
    }
}