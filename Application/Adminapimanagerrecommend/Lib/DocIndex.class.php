<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;

class DocIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {
        $p = I('p',1);
        $title = I('title');
        $type = I('doc_type');
        $service = Service\DocsService::get_instance();

        $map = \Common\Model\NfDocsModel::$type_map;

        $type_ids = array_values($map);
        $where=[];
        $where['type'] = $type_ids[0];
        if ($title) {
            $where['title'] = ['like', '%' . $title . '%'];
        }
        if ($type) {
            $where['type'] = isset($map[$type]) ? $map[$type] : 0;
        }
        list($list, $count) = $service->get_by_where($where,'id desc',$p);

        $type = $type ? $type : array_keys($map)[0];
        return result_json(TRUE, '', ['list'=>$list,'total'=>$count,'type'=>$type,'types'=>array_keys($map)]);

    }



}