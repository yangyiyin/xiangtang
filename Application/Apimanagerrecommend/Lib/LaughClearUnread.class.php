<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughClearUnread extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $type = $this->post_data['type'];
        if (!$id || !$type) {
            return result_json(false, '更新未读数失败');
        }

        //增加更新未读数

        if($type=='page') {
            $PageService = Service\PageService::get_instance();
            $PageService->update_by_where(['uid'=>$this->uid, 'id'=>$id],['unread_count'=>0]);
        } else {
            $UserPageService = Service\UserPageService::get_instance();
            $UserPageService->update_by_where(['uid'=>$this->uid, 'page_id'=>$id],['unread_count'=>0]);
        }
        
        return result_json(TRUE, '操作成功!');
    }

}