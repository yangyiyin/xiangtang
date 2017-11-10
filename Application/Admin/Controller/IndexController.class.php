<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi as UserApi;

/**
 * 后台首页控制器
 * @author Jroy
 */
class IndexController extends AdminController {

    /**
     * 后台首页
     * @author Jroy
     */
    public function index(){
        if(UID){
//            $this->meta_title = '管理首页';
//            $this->display();
            $du_info = D('FinancialDepartmentUid')->where(['uid'=>UID])->find();

            if ($du_info) {
                $DepartmentService = \Common\Service\DepartmentService::get_instance();
                $info = $DepartmentService->get_info_by_id($du_info['did']);
                if ($info) {
                    $type = $info['type'];

                    switch ($type) {
                        case 1:
                            $this->redirect('FinancialInsuranceProperty/index_list', ['menuId' => 317]);
                        case 2:
                            $this->redirect('FinancialInsuranceLife/index_list', ['menuId' => 325]);
                        case 3:
                            $this->redirect('FinancialInsuranceMutual/index_list', ['menuId' => 329]);
                        case 4:
                            $this->redirect('FinancialVouch/index_list', ['menuId' => 333]);
                        case 5:
                            $this->redirect('FinancialInvestment/index_list', ['menuId' => 343]);
                        case 6:
                            $this->redirect('FinancialInvestmentManager/index_list', ['menuId' => 338]);
                        case 7:
                            $this->redirect('FinancialFutures/index_list', ['menuId' => 358]);
                        case 8:
                            $this->redirect('FinancialLease/index_list', ['menuId' => 363]);
                        case 9:
                            $this->redirect('FinancialLoan/index_list', ['menuId' => 368]);
                        case 10:
                            $this->redirect('FinancialSecurities/index_list', ['menuId' => 373]);
                        case 11:
                            $this->redirect('FinancialTransferFunds/index_list', ['menuId' => 378]);
                        case 12:
                            $this->redirect('FinancialBank/index_list', ['menuId' => 383]);
                        case 13:
                            //金融办
                            //获取最新的提交情况
                            $VerifyService = \Common\Service\VerifyService::get_instance();
                            $where = [];
                            $where['status'] = \Common\Model\FinancialVerifyModel::STATUS_SUBMIT;
                            $where['type'] = ['in', [\Common\Model\FinancialVerifyModel::TYPE_BANK_MONTH, \Common\Model\FinancialVerifyModel::TYPE_BANK_quarter]];
                            list($list, $count) = $VerifyService->get_by_where($where, 'modify_time desc, id desc', 1);
                            $this->assign('list', $list);

                            $this->display();
                            exit();
                        case 14:
                            //保险协会

                            $VerifyService = \Common\Service\VerifyService::get_instance();
                            $where = [];
                            $where['status'] = \Common\Model\FinancialVerifyModel::STATUS_SUBMIT;
                            $where['type'] = ['in', [\Common\Model\FinancialVerifyModel::TYPE_FinancialInsuranceProperty, \Common\Model\FinancialVerifyModel::TYPE_FinancialInsuranceLife]];
                            list($list, $count) = $VerifyService->get_by_where($where, 'modify_time desc, id desc', 1);
                            $this->assign('list', $list);

                            $this->display();
                            exit();
                    }

                } else {

                }
            } else {

            }

            $this->display('index_error');

        } else {
            $this->redirect('Public/login');
        }
    }

    //清楚缓存
    public function clean()
    {
        if(sp_clear_cache()){
            $this->success('缓存已清除');
        }else{
            $this->error('缓存清楚失败!');
        }
    }

    public function setKey($cid = null,$title = null)
    {
        $title = urldecode($_GET['title']);


        if(!$title){
            $this->error('只支持主栏目快捷');
        }
        $url = $_SERVER['HTTP_REFERER'];

        $hotkey = array();
        if(cookie('Admin_hotkey')){
            $hotkey = cookie('Admin_hotkey');
            $hotkey = json_decode($hotkey,true);
            $hotkey[$title] = array(
                'title' => $title,
                'url'   => $url,
            );
            cookie('Admin_hotkey',json_encode($hotkey));
        }else{
            $hotkey[$title] = array(
                'title' => $title,
                'url'   => $url,
            );
            cookie('Admin_hotkey',json_encode($hotkey));
        }
        $this->success('快捷导航添加成功！');
    }

    public function cutKey($title = ''){
        $title = urldecode($_GET['title']);
        if(!$title){
            $this->error('参数错误');
        }
        $hotkey = json_decode(cookie('Admin_hotkey'),true);
        unset($hotkey[$title]);
        cookie('Admin_hotkey',json_encode($hotkey));
        $this->success('快捷导航已删除');
    }
}
