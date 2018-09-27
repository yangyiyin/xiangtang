<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMyTmplist extends BaseApi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $type = I('type',0);
        $UserTemplateService = \Common\Service\UserTemplateService::get_instance();
        $mylist = $UserTemplateService->get_by_uid($this->uid, $type);
        $tids = result_to_array($mylist, 'tid');
        $tids = array_slice($tids, 0, 30);//取最新的20个
        $TemplateService = \Common\Service\TemplateService::get_instance();
        $tmp_list = $TemplateService->get_by_type_ids($type, $tids);
        $tmp_list = $this->convert($tmp_list);
        $tmp_list_map = result_to_map($tmp_list);
        $new_list = [];
        foreach ($tids as $tid) {
            if (isset($tmp_list_map[$tid])) {
                $new_list[] = $tmp_list_map[$tid];
            }
        }
        return result_json(TRUE, '获取成功',$new_list);
    }


    public function convert($list) {
        if ($list) {
            foreach ($list as $k => $value) {
                switch ($value['type']) {
                    case \Common\Service\TemplateService::TYPE_QUICK_BUY:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/lable_discount.png';
                        break;
                    case \Common\Service\TemplateService::TYPE_CUT_PRICE:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/lable_cutprice.png';
                        break;
                    case \Common\Service\TemplateService::TYPE_PRAISE:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/label_collect.png';
                        break;
                    case \Common\Service\TemplateService::TYPE_VOTE:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/lable_vote.png';
                        break;
                    case \Common\Service\TemplateService::TYPE_TUWEN:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/label_image.png';
                        break;
                    case \Common\Service\TemplateService::TYPE_SIGN:
                        $list[$k]['icon'] = 'http://paz3jxo1v.bkt.clouddn.com/label_sign%20up.png';
                        break;
                }
            }
        }
        return $list;
    }

}