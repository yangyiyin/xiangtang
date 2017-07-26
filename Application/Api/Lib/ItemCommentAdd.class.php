<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ItemCommentAdd extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $ItemCommentService;
    public function init() {
        $this->ItemCommentService = Service\ItemCommentService::get_instance();
    }

    public function excute() {
        $iid = $this->post_data['item_id'];
        $sku_id = $this->post_data['sku_id'];

        $comment = $this->post_data['comment'];
        $comment_id = $this->post_data['comment_id'];

        $data = [];
        $data['iid'] = $iid;
        $data['sku_id'] = $sku_id;
        $data['comment'] = $comment;
        $data['pid'] = intval($comment_id);
        $data['uid'] = $this->uid;
        $data['product_id'] = 0;

        $ret = $this->ItemCommentService->add_one($data);

        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        return result_json(TRUE, '添加成功');
    }

}