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
            $tmp_data = $info['content'] = json_decode($info['tmp_data'],true);
            foreach ($info['content']['page'] as $k => $_page) {
                if ($_page['type'] == 'text') {
                    $info['content']['page'][$k]['text'] = str_replace("<br/>","\n",$_page['text']);
                }
            }

            $info['sign_list'] = $info['praise_list'] = $info['cutprice_list'] = $info['vote_list']=[];
            if ($tmp_data['sign_list']) {
                $PageSignService = \Common\Service\PageSignService::get_instance();
                $sign_list = $PageSignService->get_by_page_id($id);
                $sign_list = $this->convert($sign_list);
                $info['sign_list'] = $sign_list;
            }

            if ($tmp_data['praise_list']) {
                $PageSignService = \Common\Service\PagePraiseService::get_instance();
                $list = $PageSignService->get_by_page_id($id);
                $list = $this->convert($list);
                $info['praise_list'] = $list;

                $extra_uid = I('extra_uid');
                $all_log = $PageSignService->get_by_uid_page_id_all(1,$id);
                $is_sign_praise = $is_help_praise = 0;
                if ($all_log) {
                    foreach ($all_log as $log) {
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
                $list = $PageCutpriceService->get_by_page_id($id);
                $list = $this->convert($list);
                $info['cutprice_list'] = $list;

                $extra_uid = I('extra_uid');
                $all_log = $PageCutpriceService->get_by_uid_page_id_all(1,$id);
                $is_sign_cutprice = $is_help_cutprice = 0;
                if ($all_log) {
                    foreach ($all_log as $log) {
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

            $info['page_url'] = 'https://www.88plus.net/public/index.php/HomeManagerRecommend/Pages/index.html?id=' . $id;
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