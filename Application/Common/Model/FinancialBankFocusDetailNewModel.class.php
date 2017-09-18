<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Model;
use Think\Model;
class FinancialBankFocusDetailNewModel extends NfBaseModel {
    protected $_validate = array(
        array('all_name', 'require', '公司全称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filler_man', 'require', '填表人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('year', 'number', '年份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('month', 'number', '月份必须是数字', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),

        array('Industry', 'require', '所属行业', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Scale', 'require', '规模', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Principal', 'require', '法人', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Address', 'require', '注册地址', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Phone', 'require', '联系电话', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Area', 'require', '街道', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Startdate', 'require', '发放日期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('Enddate', 'require', '到期日期', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),


    );

    protected $_auto = array (
        array('ip','set_ip',self::MODEL_BOTH,'callback'),

    );

    protected function set_ip($ip){
        return $_SERVER["REMOTE_ADDR"];
    }


}