<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
class MenuAddedit extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    public function init() {

    }

    public function excute() {

        $post = $this->post_data['data'];
//        var_dump($post);
        $data = [];
        $Menu = D('Menu');
        if ($this->post_data['action'] == 'add') {
            $data['title'] = $post['name'];
            $data['pid'] = $post['pid'] ? $post['pid'] : 0;
            $data['sort'] = $post['listorder'];
            $data['module'] = $post['module'];
            $data['url'] = $post['url'];
            $data['hide'] = $post['hide'];
            $data['ico'] = $post['ico'];
            $ret = $Menu->create($data);
            if($ret){
                $id = $Menu->add();
                if($id){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_user_log('新增菜单');
                    return result_json(TRUE, '新增成功', 1);
                } else {
                    return result_json(FALSE, '新增失败', 0);
                }
            } else {
                return result_json(FALSE, $Menu->getError(), 0);
            }
        } elseif ($this->post_data['action'] == 'edit') {
            $data['id'] = $post['id'];
            $data['title'] = $post['name'];
            $data['sort'] = $post['listorder'];
            $data['module'] = $post['module'];
            $data['url'] = $post['url'];
            $data['hide'] = $post['hide'];
            $data['ico'] = $post['ico'];

            $ret = $Menu->create($data);
            if($ret){
                if($Menu->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_user_log('修改菜单');
                    return result_json(TRUE, '修改成功', 1);
                } else {
                    //var_dump($Menu->getlastsql());die;
                    return result_json(FALSE, '修改失败', 0);
                }
            } else {
                return result_json(FALSE, $Menu->getError(), 0);
            }
        }

        return result_json(TRUE, '操作成功', 1);


    }

}