<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class ItemCommentList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $ItemCommentService;
    public function init() {
        $this->ItemCommentService = Service\ItemCommentService::get_instance();
    }

    public function excute() {
        $iid = I('get.item_id');
        $sku_id = I('get.sku_id');
        if ($sku_id) {
            $comments = $this->ItemCommentService->get_by_sku_id($sku_id);
        } elseif ($iid) {
            $comments = $this->ItemCommentService->get_by_iid($iid);
        } else {
            return result_json(false, '参数错误');
        }
        $comments_new = [];
        foreach ($comments as $comment) {
            $comments = convert_objs($comments, 'id,item_id,pid,sku_id,comment,child');
            $temp = [];
            $temp['id'] = (int) $comment['id'];
            $temp['item_id'] = (int) $comment['iid'];
            $temp['pid'] = (int) $comment['pid'];
            $temp['sku_id'] = (int) $comment['sku_id'];
            $temp['comment'] = $comment['comment'];
            //$temp['child'] = $comment['child'];
            $comments_new[] = $temp;
        }
        $comments = make_tree(result_to_complex_map($comments_new, 'pid'));
        return result_json(TRUE, '', $comments);
    }

}