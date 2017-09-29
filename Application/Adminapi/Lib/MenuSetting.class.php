<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
class MenuSetting extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {

    }

    public function excute() {

        $tree = new \Org\Util\Tree;
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $result = M('menu')->order(array("sort"=>"asc"))->select();

        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . U("menu/edit", array("id" => $r['id'])) . '">修改</a> | <a class="J_ajax_get confirm" href="' . U("menu/del", array("id" => $r['id'])) . '">删除</a> ';
            $r['id']=$r['id'];
            $r['parentid']=$r['pid'];
            $r['name']=$r['title'];
            $r['listorder'] = $r['sort'];
            $array[] = $r;
        }
        $tree->init($array);
        $str = "<tr data-parentid='\$parentid' data-id='\$id'>
                    <td><input name='ids[\$id]' type='text' value='\$listorder' class='input input-xsmall'></td>
                    <td>\$id</td>
                    <td>\$spacer\$name</td>
                    <td>\$module</td>
                    <td>\$url</td>
                    <td>\$hide</td>
                    <td>\$str_manage</td>
                </tr>";
        $taxonomys = $tree->get_tree_array(0, $str);

        //Cookie('__forward__',U('Menu/index'));
        //$this->assign("taxonomys", $taxonomys);
        //$this->display();


        return result_json(TRUE, '', $taxonomys);


    }

}