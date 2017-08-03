<?php
/**
 * Created by newModule.
 * Time: 2017-08-02 11:12:29
 */
namespace Common\Model;
use Think\Model;
class FinancialInvestmentExitModel extends NfBaseModel {
    const EXIT_METHOD_A = 1;
    const EXIT_METHOD_B = 2;
    const EXIT_METHOD_C = 3;
    const EXIT_METHOD_D = 4;
    const EXIT_METHOD_E = 5;
    public static $EXISTS_METHOD_MAP = [1=>'上市退出', 2=>'管理层回购退出', 3=>'股权转让退出', 4=>'企业并购退出', 5=>'其他退出方式'];
    protected $patchValidate = true;
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Name', 'require', '所管理公司名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Startdate', 'require', '起始日期不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Exitdate', 'require', '退出日期不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Investment', 'currency', '请检查原始投资额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Recycling', 'currency', '请检查回收额格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('ExitMethod', 'number', '请检查投资项目退出方式格式', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

    );

    protected $_auto = array (
        array('Startdate','set_Startdate',self::MODEL_BOTH,'callback'),
        array('Exitdate','set_Exitdate',self::MODEL_BOTH,'callback'),


    );

    protected function set_Startdate($data) {
        //获得去年的income
        return strtotime($data['Startdate']);
    }

    protected function set_Exitdate($data) {
        //获得去年的income
        return strtotime($data['Exitdate']);
    }


}