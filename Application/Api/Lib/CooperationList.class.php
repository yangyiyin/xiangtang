<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class CooperationList extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $CooperationService;
    public function init() {
        $this->CooperationService = Service\CooperationService::get_instance();
    }

    public function excute() {
        $p = I('p',1);
        $result = [];
        $not_in_ids = [];
        if ($p == 1) {
            //获取推荐
            $CooperationBlockService = \Common\Service\CooperationBlockService::get_instance();
            $recomemd = $CooperationBlockService->get_by_type(\Common\Model\NfCooperationBlockModel::TYPE_RECOMMEND);
            $recommed_cids = result_to_array($recomemd, 'cid');
            if ($recommed_cids) {
                $not_in_ids = array_merge($not_in_ids, $recommed_cids);
                $recommed_list = $this->CooperationService->get_by_ids($recommed_cids);
                $recommed_list = $this->convert($recommed_list);
                $recommed_list = convert_objs($recommed_list,'id,title,img');
                $result['recommed_list'] = $recommed_list;
            } else {
                $result['recommed_list'] = [];
            }

            //获取促销
            $CooperationBlockService = \Common\Service\CooperationBlockService::get_instance();
            $promotion = $CooperationBlockService->get_by_type(\Common\Model\NfCooperationBlockModel::TYPE_PROMOTION);
            $promotion_cids = result_to_array($promotion, 'cid');
            if ($promotion_cids) {
                $not_in_ids = array_merge($not_in_ids, $promotion_cids);
                $promotion_list = $this->CooperationService->get_by_ids($promotion_cids);
                $promotion_list = $this->convert($promotion_list);
                $promotion_list = convert_objs($promotion_list,'id,title');
                $result['promotion_list'] = $promotion_list;
            } else {
                $result['promotion_list'] = [];
            }

        } else {
            $result['recommed_list'] = [];
            $result['promotion_list'] = [];
        }

        $where = [];
        if ($not_in_ids) {
            $where['id'] = ['not in', $not_in_ids];
        }

        list($data,$count) = $this->CooperationService->get_by_where($where,'id desc',$p);
        $data = $this->convert($data);
        $data = convert_objs($data,'id,title');
        $result['list'] = $data;

        if ($result['recommed_list']) {
            $result['list'] = array_merge($result['recommed_list'], $result['list']);
            unset($result['recommed_list']);
        }

        if ($result['promotion_list']) {
            $result['list'] = array_merge($result['promotion_list'], $result['list']);
            unset($result['promotion_list']);
        }

        $result['has_more'] = has_more($count,$p, Service\CooperationService::$page_size);
        return result_json(TRUE, '', $result);
        
    }

    private function convert($data) {
        if ($data) {
            foreach ($data as $key => $_item) {
                if ($_item['img']) {
                    $data[$key]['img'] = item_img(get_cover($_item['img'], 'path'));
                }

            }
        }

        return $data;

    }

}