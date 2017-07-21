<?php
/**
 * Created by newModule.
 * Time: 2017-07-11 14:41:08
 */
namespace Admin\Controller;

class AntPropertyController extends AdminController {
    private $PropertyService;
    private $PropertyValueService;
    protected function _initialize() {
        parent::_initialize();
        $this->PropertyService = \Common\Service\PropertyService::get_instance();
        $this->PropertyValueService = \Common\Service\PropertyValueService::get_instance();
    }

    public function index() {

        $where = [];
        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        $page = I('get.p', 1);
        list($data, $count) = $this->PropertyService->get_by_where($where, 'id desc', $page);
        $PageInstance = new \Think\Page($count, \Common\Service\PropertyService::$page_size);
        if($total>\Common\Service\PropertyService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();

        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }



    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->PropertyService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除产品属性');
        $this->success('删除成功！');
    }

    public function add() {
        $labels = '';
        if ($id = I('get.id')) {
            $info = $this->PropertyService->get_info_by_id($id);
            if ($info) {
                $values = $this->PropertyValueService->get_by_property_id($info['id']);
                $names = result_to_array($values, 'name');
                $labels = join(',',$names);
                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
        }
        $this->assign('labels', $labels);
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');

           if (I('post.labels')) {
               $values = explode(',', I('post.labels'));
           } else {
               $values = '';
           }

            if ($id) {
                $ret = $this->PropertyService->update_by_id($id, $data);
                $this->PropertyValueService->del_by_property_id($id);
                //添加属性值
                if ($values) {
                    $data = [];
                    foreach ($values as $name) {
                        if ($name) {
                            $data[] = ['property_id'=> $id, 'name'=> $name];
                        }
                    }
                    if ($data) {
                        // print_r($data);die();
                        $this->PropertyValueService->add_batch($data);
                    }

                }

                if ($ret->success) {
                    action_user_log('修改产品属性');
                    $this->success('修改成功！', U('index'));
                } else {
                    $this->error('修改属性名称失败,修改属性值成功,请核实');
                }
            } else {
                $ret = $this->PropertyService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加产品属性');

                    //添加属性值
                    if ($values) {

                        $data = [];
                        foreach ($values as $name) {
                            if ($name) {
                                $data[] = ['property_id'=> $ret->data, 'name'=> $name];
                            }
                        }
                        if ($data) {
                           // print_r($data);die();
                            $this->PropertyValueService->add_batch($data);
                        }
                    }

                    $this->success('添加成功！', U('index'));
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

    public function search() {
        $property_name = I('post.property_name');
        $info = $this->PropertyService->get_like_name($property_name);
        if ($info) {
            $this->ajaxReturn($info);
        } else {
            $this->ajaxReturn('');
        }
    }

    public function add_cid_property() {
        $cid = I('post.cid');
        $property_name = I('post.property_name');
        $info = $this->PropertyService->get_by_name($property_name);
        if (!$info) {
            $this->ajaxReturn(result(false, '添加失败'));
            //$this->ajaxReturn($info);
        }

        $CatPropertyService = \Common\Service\CatPropertyService::get_instance();
        $ret = $CatPropertyService->get_by_cid_pid($cid, $info['id']);
        if ($ret) {
            $this->ajaxReturn(result(false, '已存在该属性'));
        }
        $data['cid'] = $cid;
        $data['pid'] = $info['id'];
        $data['p_name'] = $info['name'];
        $CatPropertyService->add_one($data);

        $this->ajaxReturn(result(true, '添加失败'));

    }

    public function get_cat_property(){
        $cid = I('post.cid');
        $CatPropertyService = \Common\Service\CatPropertyService::get_instance();
        $data = $CatPropertyService->get_by_cid($cid);

        if ($data) {
            $this->ajaxReturn($data);
        } else {
            $this->ajaxReturn([]);
        }
    }

    public function get_cat_property_values(){
        $cid = I('get.cid');
        $CatPropertyService = \Common\Service\CatPropertyService::get_instance();
        $PropertyValueService = \Common\Service\PropertyValueService::get_instance();

        $data = $CatPropertyService->get_by_cid($cid);
        $map = [];
        if ($data) {
            //var_dump($data);die();
            $property_ids = result_to_array($data, 'pid');
           // var_dump($property_ids);die();
            $p_values = $PropertyValueService->get_by_property_ids($property_ids);
            $p_values_map = result_to_complex_map($p_values, 'property_id');
            foreach ($data as $_property) {
                if (isset($p_values_map[$_property['pid']])) {
                    $map[] = ['property'=>$_property['p_name'], 'values' => $p_values_map[$_property['pid']]];
                }
            }
        }

        $this->ajaxReturn($map);
    }

    public function del_cat_property() {
        $cid = I('post.cid');
        $pid = I('post.pid');

        $CatPropertyService = \Common\Service\CatPropertyService::get_instance();
        $data = $CatPropertyService->get_by_cid_pid($cid, $pid);

        if ($data) {
            $CatPropertyService->del_by_cid_pid($cid, $pid);
            $this->ajaxReturn(result(true, '删除成功'));
        }


        $this->ajaxReturn(result(false, '找不到对应的属性,请重试'));

    }
}