<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class DocController extends Controller {


	//系统首页
    public function index(){
        $title = I('title');
        $type = I('doc_type');
        $service = \Common\Service\DocsService::get_instance();

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
        $list = $service->get_by_where_all($where);

        $type = $type ? $type : array_keys($map)[0];
//        return result_json(TRUE, '', ['list'=>$list,'total'=>$count,'type'=>$type,'types'=>array_keys($map)]);

        $this->convert_data($list);
        $this->assign('list', $list);


        $options = '';
        foreach ($map as $name => $id) {
            if ($name == $type) {
                $options .= '<option value="'.$name.'" selected="selected">'.$name.'</option>';
            } else {
                $options .= '<option value="'.$name.'">'.$name.'</option>';
            }
        }
        $this->assign('type_options', $options);
        $this->assign('title', $title);

        $this->display();


    }


    private function convert_data(&$list) {
        if ($list) {
            $map = \Common\Model\NfDocsModel::$type_map;
            $map = array_flip($map);
            foreach ($list as $key => $li) {
                $list[$key]['type_desc'] = isset($map[$li['type']]) ? $map[$li['type']] : '';
                $list[$key]['content_from'] = str_replace("\n", '<br/>', $li['content_from']);
                $list[$key]['content_to'] = str_replace("\n", '<br/>', $li['content_to']);
            }
        }
    }





}