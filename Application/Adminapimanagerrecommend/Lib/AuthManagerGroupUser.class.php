<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
class AuthManagerGroupUser extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {
        $group_id = I('gid');
        if(empty($group_id)){
            return result_json(false, '参数错误', []);
        }


        if ($group_id == C('GROUP_FRANCHISEE') && !I('dev_y')) {
            return result_json(false, '加盟商组不支持人为管理,请联系技术', []);

        }

        $auth_group = M('AuthGroup')->where( array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN) )
            ->getfield('id,id,title,rules');
        $prefix   = C('DB_PREFIX');
        $l_table  = $prefix.(AuthGroupModel::MEMBER);
        $r_table  = $prefix.(AuthGroupModel::AUTH_GROUP_ACCESS);
        $model    = M()->table( $l_table.' m' )->join ( $r_table.' a ON m.uid=a.uid' );
        $_REQUEST = array();
        list($list,$count) = $this->lists($model,array('a.group_id'=>$group_id,'m.status'=>array('egt',0)),'m.uid asc',null,'m.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status');
        $this->int_to_string($list);

        return result_json(TRUE, '', ['list'=>$list,'total'=>$count]);


    }

    protected function lists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        //$this->assign('_page', $p? $p: '');
        //$this->assign('_total',$total);
       // $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return [$model->field($field)->select(), $total];
    }

    private function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
        if($data === false || $data === null ){
            return $data;
        }
        $data = (array)$data;
        foreach ($data as $key => $row){
            foreach ($map as $col=>$pair){
                if(isset($row[$col]) && isset($pair[$row[$col]])){
                    $data[$key][$col.'_text'] = $pair[$row[$col]];
                }
            }
            $data[$key]['last_login_time'] = date('Y-m-d H:i:s', $data[$key]['last_login_time']);
            $data[$key]['last_login_ip'] = long2ip($data[$key]['last_login_ip']);

        }
        return $data;
    }

}