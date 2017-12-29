<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
class MenuIndex extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $AdService;
    public function init() {
        $this->AdService = Service\AdService::get_instance();
    }

    public function excute() {
        if(empty($menus)){
            // 获取主菜单
            $where['pid']   =   0;
            $where['hide']  =   0;
            if(!C('DEVELOP_MODE')){ // 是否开发者模式
                $where['is_dev']    =   0;
            }
            $field = array('id,title,url,pid,ico,module');
            $menus  =   M('Menu')->field($field)->where($where)->order('sort asc,id')->select();

            foreach ($menus as $key => $item) {
                if (!is_array($item) || empty($item['title']) || empty($item['url']) ) {
                    return result_json(FALSE, '控制器基类$menus属性元素配置有误');
                }
                $menus[$key]['name'] = $item['title'];
                $menus[$key]['icon'] = $item['ico'];
                $menus[$key]['url'] = '/#/' . $item['module'].'/'.$item['url'];
                // 判断主菜单权限
                if ( !IS_ROOT && !$this->checkRule($item['url'],AuthRuleModel::RULE_MAIN,null) ) {
                    unset($menus[$key]);
                    continue;//继续循环
                }

                // 获取当前主菜单的子菜单项
                if(M('Menu')->where("pid = {$item['id']} and hide=0")->select()){
                    $groups = M('Menu')->field($field)->where("pid = {$item['id']} and hide=0")->order('sort asc,id')->distinct(true)->select();

                    $to_check_urls = array();
                    if(!IS_ROOT){
                        // 检测菜单权限
                        foreach ($groups as $k=>$v) {
                            $rule = $v['module'].'/'.$v['url'];
                            $v['url'] = $rule;
                            if($this->checkRule($rule, AuthRuleModel::RULE_URL,null)){
                                $to_check_urls[] = $v;
                            }
                        }
                    }else{
                        $to_check_urls = $groups;
                    }

                    $to_check_urls = [];
                    foreach ($groups as $k=>$v) {
                        $v['name'] = $v['title'];
                        $v['icon'] = $v['ico'];
                        $v['url'] = '/#/' . $v['url'];
                        $to_check_urls[] = $v;
                    }

                    $menus[$key]['child'] = $to_check_urls;
                }
            }
            //session('ADMIN_MENU_LIST'.$controller,$menus);
        }


        return result_json(TRUE, '', $menus);


    }

}