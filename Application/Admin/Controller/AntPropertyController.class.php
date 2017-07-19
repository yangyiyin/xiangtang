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
        $property_name = I('get.property_name');
        $info = $this->PropertyService->get_by_name($property_name);
        if ($info) {

        }


    }

}