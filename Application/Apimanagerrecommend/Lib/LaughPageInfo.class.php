<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughPageInfo extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $id = I('id');

        $PageService = \Common\Service\PageService::get_instance();
        $info = $PageService->get_info_by_id($id);

        if ($info) {
           // $info['tmp_data'] = str_replace('<br\/>',"\n",$info['tmp_data']);
           // $info['tmp_data'] = str_replace('<br>',"\n",$info['tmp_data']);

            //库存
            if ($info['stock'] > 0 && ($info['stock'] - $info['sell_num']) <= 0) {
                $info['stock_none'] = true;
            } else {
                $info['stock_none'] = false;
            }


            $tmp_data = $info['content'] = json_decode($info['tmp_data'],true);
            foreach ($info['content']['page'] as $k => $_page) {
                if ($_page['type'] == 'text') {
                    $info['content']['page'][$k]['text'] = str_replace("<br/>","\n",$_page['text']);
                }
            }

            $info['sign_list'] = $info['praise_list'] = $info['cutprice_list'] = $info['vote_list']= $info['fight_group_list'] = $info['quick_buy_list'] = [];
            $info['pick_code'] = '';
            if ($tmp_data['sign_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;

                $all_log = $PageSignService->get_by_uid_page_id_all($this->uid,$id);
                if ($all_log) {
                    foreach ($all_log as $log) {
                        $log['pick_code'] && $info['pick_code'] = $log['pick_code'];
                        if ($log['pick_status'] != \Common\Service\PageBaseService::pick_status_init) {
                            $info['pick_code'] = '您的凭证码已失效';
                        }
                    }
                }
            }

            if ($tmp_data['praise_list']) {
                $PageSignService = \Common\Service\PagePraiseService::get_instance();
                $list = $PageSignService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['praise_list'] = $list;

                $extra_uid = I('extra_uid');
                $all_log = $PageSignService->get_by_uid_page_id_all($this->uid,$id);
                $is_sign_praise = $is_help_praise = 0;
                if ($all_log) {
                    foreach ($all_log as $log) {
                        $log['pick_code'] && $info['pick_code'] = $log['pick_code'];
                        if ($log['pick_status'] != \Common\Service\PageBaseService::pick_status_init) {
                            $info['pick_code'] = '您的凭证码已失效';
                        }
                        if ($log['pid'] == 0) {
                            $is_sign_praise = 1;
                        } else {
                            if ($extra_uid == $log['pid']) {
                                $is_help_praise = 1;
                            }

                        }
                    }
                }

                $info['is_sign_praise'] = $is_sign_praise;
                $info['is_help_praise'] = $is_help_praise;
                $info['extra_uid'] = $extra_uid;
            }

            if ($tmp_data['cutprice_list']) {
                $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
                $list = $PageCutpriceService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['cutprice_list'] = $list;

                $extra_uid = I('extra_uid');
                $all_log = $PageCutpriceService->get_by_uid_page_id_all($this->uid,$id);
                $is_sign_cutprice = $is_help_cutprice = 0;
                if ($all_log) {
                    foreach ($all_log as $log) {
                        $log['pick_code'] && $info['pick_code'] = $log['pick_code'];
                        if ($log['pick_status'] != \Common\Service\PageBaseService::pick_status_init) {
                            $info['pick_code'] = '您的凭证码已失效';
                        }
                        if ($log['pid'] == 0) {
                            $is_sign_cutprice = 1;
                        } else {
                            if ($extra_uid == $log['pid']) {
                                $is_help_cutprice = 1;
                            }

                        }
                    }
                }

                $info['is_sign_cutprice'] = $is_sign_cutprice;
                $info['is_help_cutprice'] = $is_help_cutprice;
                $info['extra_uid'] = $extra_uid;
            }

            if ($tmp_data['vote_list']) {
                $PageSortService = \Common\Service\PageSortService::get_instance();
                $list = $PageSortService->get_by_page_id($id);
                $key = 0;
                foreach ($info['content']['page'] as $_key => $_page) {
                    if ($_page['type'] == 'vote') {
                        $key = $_key;
                        break;
                    }
                }
                $info['content']['page'][$key]['vote_num_arr'] = $this->convert_vote_list($info['content']['page'][$key]['vote_num_arr'], $list);
            }

            if ($tmp_data['fight_group_list']) {
                $PageFightgroupService = \Common\Service\PageFightgroupService::get_instance();
                $list = $PageFightgroupService->get_by_page_id($id,1);
                $list = $this->convert($list);
                $info['fight_group_list'] = $list;

                $extra_uid = I('extra_uid');
                $all_log = $PageFightgroupService->get_by_uid_page_id_all($this->uid,$id);
                $is_sign_fightgroup = $is_help_fightgroup = 0;

                if ($all_log) {
                    foreach ($all_log as $log) {

                        $log['pick_code'] && $info['pick_code'] = $log['pick_code'];
                        if ($log['pick_status'] != \Common\Service\PageBaseService::pick_status_init) {
                            $info['pick_code'] = '您的凭证码已失效';
                        }
                        if ($log['pid'] == 0) {
                            $is_sign_fightgroup = 1;
                        } else {
                            if ($extra_uid == $log['pid']) {
                                $is_help_fightgroup = 1;
                            }

                        }
                    }
                }

                $info['is_sign_fightgroup'] = $is_sign_fightgroup;
                $info['is_help_fightgroup'] = $is_help_fightgroup;
                $info['extra_uid'] = $extra_uid;
            }

            if ($tmp_data['quick_buy_list']) {
                $PageQuickbuyService = \Common\Service\PageQuickbuyService::get_instance();
                $quick_buy_list = $PageQuickbuyService->get_by_page_id($id);
                $quick_buy_list = $this->convert($quick_buy_list);
                $info['quick_buy_list'] = $quick_buy_list;

                $all_log = $PageQuickbuyService->get_by_uid_page_id_all($this->uid,$id);
                if ($all_log) {
                    foreach ($all_log as $log) {
                        $log['pick_code'] && $info['pick_code'] = $log['pick_code'];
                        if ($log['pick_status'] != \Common\Service\PageBaseService::pick_status_init) {
                            $info['pick_code'] = '您的凭证码已验证';
                        }
                    }
                }
            }

            $info['page_url'] = 'https://www.'.C('BASE_WEB_HOST').'/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $id;
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

                //拼团
                if (isset($value['group'])) {
                    $list[$k]['group'] = json_decode($value['group'], true);
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