<?php

// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> 
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台配置控制器
 * @author yangweijie <yangweijiester@gmail.com>
 */
class MenuController extends AdminController {
    protected $Menu;

    public function add_menu_direct() {

        $arr = [
            ['title' => '银行机构填报本单位数据','url'=>'FinancialBank/credit_new_submit_monthly','urls'=>'FinancialBank/credit_new_submit_monthly,FinancialBank/baddebt_new_submit_monthly,FinancialBank/baddebt_detail_new_submit_monthly,FinancialBank/baddebt_dispose_new_submit_monthly,
            FinancialBank/focus_detail_new_submit_monthly,FinancialBank/quarterly_quantity_a_new_submit_monthly,FinancialBank/quarterly_quantity_b_new_submit_monthly,FinancialBank/quarterly_quantity_c_new_submit_monthly,FinancialBank/submit_monthly_verify_new,FinancialBank/upload_excel','pid'=>212,'module'=>'Admin','sort'=>0,'hide'=>1],

            ['title' => '银行机构填报所有单位数据','url'=>'FinancialBank/submit_monthly_all','urls'=>'FinancialBank/submit_monthly_all,FinancialBank/credit_new_submit_monthly,FinancialBank/baddebt_new_submit_monthly,FinancialBank/baddebt_detail_new_submit_monthly,FinancialBank/baddebt_dispose_new_submit_monthly,
            FinancialBank/focus_detail_new_submit_monthly,FinancialBank/quarterly_quantity_a_new_submit_monthly,FinancialBank/quarterly_quantity_b_new_submit_monthly,FinancialBank/quarterly_quantity_c_new_submit_monthly,FinancialBank/submit_monthly_verify_new,FinancialBank/upload_excel','pid'=>212,'module'=>'Admin','sort'=>0,'hide'=>1],

            ['title' => '银行机构查看本单位数据', 'url'=>'FinancialBank/index_list','urls'=>'FinancialBank/index_list,FinancialBank/get_detail_page_html','pid'=>212,'module'=>'Admin','sort'=>0,'hide'=>0],
            ['title' => '银行机构查看所有单位数据', 'url'=>'FinancialBank/index_all_list','urls'=>'FinancialBank/index_all_list,FinancialBank/get_detail_page_html','pid'=>212,'module'=>'Admin','sort'=>0,'hide'=>0],
            ['title' => '银行机构审核所有单位数据', 'url'=>'FinancialBank/verify_change_status','urls'=>'FinancialBank/verify_change_status','pid'=>212,'module'=>'Admin','sort'=>0,'hide'=>1],


        ];


//        $data = [];
//        foreach ($arr as $_value) {
//            $data[] = ['title'=>$_value['title'], 'pid'=>212, 'module'=>'Admin', 'sort'=>99, 'url'=>$_value['url'], 'hide'=>1,'icon'=>'fa-square'];
//        }

        M('menu')->addAll($arr);
    }


    public function add_menu_direct_all() {

        $arr = [
            [
                'title' => '报表记录',
                'url'=>'FinancialSubmitLog/index',
                'child' => [
                    ['title' => '财产保险报表记录', 'url'=>'FinancialInsuranceProperty/submit_log'],
                    ['title' => '人身保险报表记录', 'url'=>'FinancialInsuranceLife/submit_log'],
                    ['title' => '保险互助社报表记录', 'url'=>'FinancialInsuranceMutual/submit_log'],
                    ['title' => '担保公司报表记录', 'url'=>'FinancialVouch/submit_log'],
                    ['title' => '股权投资和创业投资机构报表记录', 'url'=>'FinancialInvestment/submit_log'],
                    ['title' => '股权投资管理机构报表记录', 'url'=>'FinancialInvestmentManager/submit_log'],
                    ['title' => '期货营业部报表记录', 'url'=>'FinancialFutures/submit_log'],
                    ['title' => '融资租赁报表记录', 'url'=>'FinancialLease/submit_log'],
                    ['title' => '小额贷款公司报表记录', 'url'=>'FinancialLoan/submit_log'],
                    ['title' => '金融机构本外币信贷报表记录', 'url'=>'FinancialBank/credit_submit_log'],
                    ['title' => '金融机构本外币存贷款报表记录', 'url'=>'FinancialBank/loan_submit_log'],
                    ['title' => '金融机构季度报表记录', 'url'=>'FinancialBank/quarter_submit_log'],
                    ['title' => '逾期化解报表记录', 'url'=>'FinancialBank/overdue_resolve_submit_log'],
                    ['title' => '不良贷款处置报表记录', 'url'=>'FinancialBank/baddebt_dispose_submit_log'],
                    ['title' => '证券营业部报表记录', 'url'=>'FinancialSecurities/submit_log'],
                    ['title' => '转贷报表记录', 'url'=>'FinancialTransferFunds/submit_log'],
                ]
            ]
//            [
//                'title' => '担保公司',
//                'url'=>'FinancialVouch/index',
//                'child' => [
//                    ['title' => '担保公司月填报', 'url'=>'FinancialVouch/submit_monthly'],
//                    ['title' => '担保公司单位管理', 'url'=>'FinancialVouch/add_unit']
//                ]
//            ],
//            [
//                'title' => '股权投资机构',
//                'url'=>'FinancialInvestment/index',
//                'child' => [
//                    ['title' => '股权投资管理机构月填报', 'url'=>'FinancialInvestmentManager/submit_monthly'],
//                    ['title' => '股权投资和创业投资机构月填报', 'url'=>'FinancialInvestment/submit_monthly'],
//                    ['title' => '股权投资管理机构明细月填报', 'url'=>'FinancialInvestmentManager/detail_submit_monthly'],
//                    ['title' => '股权投资管理机构所投资公司明细月填报', 'url'=>'FinancialInvestment/detail_submit_monthly'],
//                    ['title' => '股权投资和创业投资机构退出项目明细月填报', 'url'=>'FinancialInvestment/exit_detail_submit_monthly'],
//                    ['title' => '股权投资管理机构单位管理', 'url'=>'FinancialInvestmentManager/add_unit'],
//                    ['title' => '股权投资和创业投资机构机构单位管理', 'url'=>'FinancialInvestment/add_unit']
//                ]
//            ],
//            [
//                'title' => '期货营业部',
//                'url'=>'FinancialFutures/index',
//                'child' => [
//                    ['title' => '期货业经营情况月填报', 'url'=>'FinancialFutures/submit_monthly'],
//                    ['title' => '期货营业部单位管理', 'url'=>'FinancialFutures/add_unit']
//                ]
//            ],
//            [
//                'title' => '融资租赁',
//                'url'=>'FinancialLease/index',
//                'child' => [
//                    ['title' => '融资租赁行业月填报', 'url'=>'FinancialLease/submit_monthly'],
//                    ['title' => '融资租赁单位管理', 'url'=>'FinancialLease/add_unit']
//                ]
//            ],
//            [
//                'title' => '小额贷款公司',
//                'url'=>'FinancialLoan/index',
//                'child' => [
//                    ['title' => '小额贷款公司月填报', 'url'=>'FinancialLoan/submit_monthly'],
//                    ['title' => '小额贷款单位管理', 'url'=>'FinancialLoan/add_unit']
//                ]
//            ],
//            [
//                'title' => '银行机构',
//                'url'=>'FinancialBank/index',
//                'child' => [
//                    ['title' => '银行机构不良贷款处置明细月填报', 'url'=>'FinancialBank/baddebt_dispose_submit_monthly'],
//                    ['title' => '银行机构信贷情况月填报', 'url'=>'FinancialBank/Credit_submit_monthly'],
//                    ['title' => '银行机构贷款明细月填报', 'url'=>'FinancialBank/loan_details_submit_monthly'],
//                    ['title' => '银行金融机构季度填报', 'url'=>'FinancialBank/quarterly_monthly'],
//                    ['title' => '银行机构逾期化解明细月填报', 'url'=>'FinancialBank/overdue_resolve_submit_monthly'],
//                    ['title' => '银行机构单位管理', 'url'=>'FinancialBank/add_unit']
//                ]
//            ],
//            [
//                'title' => '证券营业部',
//                'url'=>'FinancialSecurities/index',
//                'child' => [
//                    ['title' => '证券营业部经营情况月填报', 'url'=>'FinancialSecurities/submit_monthly'],
//                    ['title' => '证券营业部单位管理', 'url'=>'FinancialSecurities/add_unit']
//                ]
//            ],
//            [
//                'title' => '转贷',
//                'url'=>'FinancialTransferFunds/index',
//                'child' => [
//                    ['title' => '转贷资金月填报', 'url'=>'FinancialTransferFunds/submit_monthly'],
//                    ['title' => '转贷资金单位管理', 'url'=>'FinancialTransferFunds/add_unit']
//                ]
//            ]
        ];


        $data = [];
        foreach ($arr as $k => $_value) {
            $data_one = ['title'=>$_value['title'], 'pid'=>0, 'module'=>'Admin', 'sort'=>$k, 'url'=>$_value['url'], 'hide'=>0,'ico'=>'fa-square'];
            M('menu')->add($data_one);
            $id = M('menu')->getLastInsID();
            $data = [];
            foreach ($_value['child'] as $key => $child) {
                $data[] = ['title'=>$child['title'], 'pid'=>$id, 'module'=>'Admin', 'sort'=>$key, 'url'=>$child['url'], 'hide'=>0];

            }
            M('menu')->addAll($data);
        }

    }

    /**
     * 后台菜单首页
     * @return none
     */
    public function index() {
        $tree = new \Org\Util\Tree;
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $result = M('menu')->order(array("sort"=>"asc"))->select();

        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . U("menu/edit", array("id" => $r['id'])) . '">修改</a> | <a class="J_ajax_get confirm" href="' . U("menu/del", array("id" => $r['id'])) . '">删除</a> ';
            $r['id']=$r['id'];
            $r['parentid']=$r['pid'];
            $r['name']=$r['title'];
            $r['listorder'] = $r['sort'];
            $r['hide'] = ($r['hide']!=0)?'是':'否';
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
        $taxonomys = $tree->get_tree(0, $str);

        Cookie('__forward__',U('Menu/index'));
        $this->assign("taxonomys", $taxonomys);
        $this->display();
    }
    /**
     * 新增菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function add(){
        if(IS_POST){
            $Menu = D('Menu');
            $data = $Menu->create();
            if($data){
                $id = $Menu->add();
                if($id){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update_menu', 'Menu', $id, UID);
                    $this->success('新增成功', Cookie('__forward__'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $this->assign('info',array('pid'=>I('pid')));
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            $this->meta_title = '新增菜单';
            $this->display('edit');
        }
    }

    /**
     * 编辑配置
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function edit($id = 0){
        if(IS_POST){
            $Menu = D('Menu');
            $data = $Menu->create();
            if($data){
                if($Menu->save()!== false){
                    // S('DB_CONFIG_DATA',null);
                    //记录行为
                    action_log('update_menu', 'Menu', $data['id'], UID);
                    $this->success('更新成功', Cookie('__forward__'));
                } else {
                    //var_dump($Menu->getlastsql());die;
                    $this->error('更新失败');
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('Menu')->field(true)->find($id);
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);

            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            if(false === $info){
                $this->error('获取后台菜单信息错误');
            }
            $this->assign('info', $info);
            $this->meta_title = '编辑后台菜单';
            $this->display();
        }
    }

    /**
     * 删除后台菜单
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Menu')->where($map)->delete()){
            // S('DB_CONFIG_DATA',null);
            //记录行为
            action_log('update_menu', 'Menu', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    public function toogleHide($id,$value = 1){
        $this->editRow('Menu', array('hide'=>$value), array('id'=>$id));
    }

    public function toogleDev($id,$value = 1){
        $this->editRow('Menu', array('is_dev'=>$value), array('id'=>$id));
    }

    public function importFile($tree = null, $pid=0){
        if($tree == null){
            $file = APP_PATH."Admin/Conf/Menu.php";
            $tree = require_once($file);
        }
        $menuModel = D('Menu');
        foreach ($tree as $value) {
            $add_pid = $menuModel->add(
                array(
                    'title'=>$value['title'],
                    'url'=>$value['url'],
                    'pid'=>$pid,
                    'hide'=>isset($value['hide'])? (int)$value['hide'] : 0,
                    'tip'=>isset($value['tip'])? $value['tip'] : '',
                    'ico'=>$value['ico'],
                )
            );
            if($value['operator']){
                $this->import($value['operator'], $add_pid);
            }
        }
    }

    public function import(){
        if(IS_POST){
            $tree = I('post.tree');
            $lists = explode(PHP_EOL, $tree);
            $menuModel = M('Menu');
            if($lists == array()){
                $this->error('请按格式填写批量导入的菜单，至少一个菜单');
            }else{
                $pid = I('post.pid');
                foreach ($lists as $key => $value) {
                    $record = explode('|', $value);
                    if(count($record) == 2){
                        $menuModel->add(array(
                            'title'=>$record[0],
                            'url'=>$record[1],
                            'pid'=>$pid,
                            'sort'=>0,
                            'hide'=>0,
                            'tip'=>'',
                            'ico'=>'',
                        ));
                    }
                }
                $this->success('导入成功',U('index?pid='.$pid));
            }
        }else{
            $this->meta_title = '批量导入后台菜单';
            $pid = (int)I('get.pid');
            $this->assign('pid', $pid);
            $data = M('Menu')->where("id={$pid}")->field(true)->find();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 菜单排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = M('Menu')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = '菜单排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            //$ids = explode(',', $ids);

            foreach ($ids as $key=>$value){
                $res = M('Menu')->where(array('id'=>$key))->setField('sort', $value);
            }
            if($res !== false){
                $this->success('排序成功！');
            }else{
                $this->eorror('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }
}
