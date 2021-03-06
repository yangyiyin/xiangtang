<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPageDetailInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $id = I('id');

        $PageService = \Common\Service\PageService::get_instance();
        $info = $PageService->get_info_by_id($id);

        if ($info) {

            $info['sign_list'] = $info['praise_list'] = $info['cutprice_list'] = $info['vote_list'] = $info['fight_group_list'] = $info['quick_buy_list'] = [];
            if (isset($info['show_sign_list']) && $info['show_sign_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;
            }

            if (isset($info['show_cutprice_list']) && $info['show_cutprice_list']) {
                $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
                $list = $PageCutpriceService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['cutprice_list'] = $list;
            }

            if (isset($info['show_praise_list']) && $info['show_praise_list']) {
                $PageSignService = \Common\Service\PagePraiseService::get_instance();
                $list = $PageSignService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['praise_list'] = $list;
            }

            if (isset($info['show_vote_list']) && $info['show_vote_list']) {
                $PageSortService = \Common\Service\PageSortService::get_instance();
                $list = $PageSortService->get_by_page_id($id);
                $key = 0;
                foreach ($info['content']['page'] as $_key => $_page) {
                    if ($_page['type'] == 'vote') {
                        $key = $_key;
                        break;
                    }
                }
                $info['vote_list'] = $this->convert_vote_list($info['content']['page'][$key]['vote_num_arr'], $list);
            }
            if (isset($info['show_fight_group_list']) && $info['show_fight_group_list']) {
                $PageFightgroupService = \Common\Service\PageFightgroupService::get_instance();
                $list = $PageFightgroupService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['fight_group_list'] = $list;
            }

            if (isset($info['show_quick_buy_list']) && $info['show_quick_buy_list']) {
                $PageQuickbuyService = \Common\Service\PageQuickbuyService::get_instance();
                $quick_buy_list = $PageQuickbuyService->get_by_page_id($id);
                $quick_buy_list = $this->convert($quick_buy_list);
                $info['quick_buy_list'] = $quick_buy_list;
            }

            //获取统计
            $PageStatisticsService = Service\PageStatisticsService::get_instance();
            $info['view_count'] = $PageStatisticsService->count_views($info['id']);
            $info['share_count'] = $PageStatisticsService->count_shares($info['id']);
            $info['submit_count'] = $PageStatisticsService->count_submits($info['id']);
            $info['is_seller'] = ($this->uid == $info['uid']);
        }
        return result_json(TRUE, '获取成功', $info);
    }

    private function convert($list) {
        if ($list) {
            $uids = result_to_array($list, 'uid');
            $UserService = \Common\Service\UserService::get_instance();
            $users = $UserService->get_by_ids($uids);
            $users_map = result_to_map($users);
            foreach ($list as $k => $value) {
                $list[$k]['user'] = isset($users_map[$value['uid']]) ? $users_map[$value['uid']] : [];
                if (isset($value['price'])) {
                    $list[$k]['price'] = format_price($value['price']);
                }
            }
        }
        return $list;
    }

    private function convert_vote_list($vote_arr, $list) {
        if ($vote_arr) {
            $list_map = result_to_map($list, 'sort_id');
            foreach ($vote_arr as $key => $value) {
                $vote_arr[$key]['sign'] = isset($list_map[$key]) ? $list_map[$key]['sum'] : 0;

            }
        }
        return $vote_arr;
    }
}