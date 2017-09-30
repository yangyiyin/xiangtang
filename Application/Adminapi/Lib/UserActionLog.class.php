<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
use Admin\Model\MemberModel;
class UserActionLog extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    public function init() {
    }

    public function excute() {
        //获取列表数据
        $map['status']    =   array('gt', -1);
        list($list,$count)   =   $this->lists('ActionLog', $map);
        $this->int_to_string($list);
        foreach ($list as $key=>$value){
            $model_id                  =   $this->get_document_field($value['model'],"name","id");
            $list[$key]['model_id']    =   $model_id ? $model_id : 0;
        }

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
        $options['limit'] = $page->firstRow.','.$page->listRows;

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
            $data[$key]['create_time'] = date('Y-m-d H:i:s', $data[$key]['create_time']);
            $data[$key]['action_id'] = $this->get_action($data[$key]['action_id'],'title');
            $data[$key]['user_id'] = get_nickname($data[$key]['user_id']);
        }
        return $data;
    }

    private function get_action($id = null, $field = null){
        if(empty($id) && !is_numeric($id)){
            return false;
        }
        $list = S('action_list');
        if(empty($list[$id])){
            $map = array('status'=>array('gt', -1), 'id'=>$id);
            $list[$id] = M('Action')->where($map)->field(true)->find();
        }
        return empty($field) ? $list[$id] : $list[$id][$field];
    }
    private function get_document_field($value = null, $condition = 'id', $field = null){
        if(empty($value)){
            return false;
        }

        //拼接参数
        $map[$condition] = $value;
        $info = M('Model')->where($map);
        if(empty($field)){
            $info = $info->field(true)->find();
        }else{
            $info = $info->getField($field);
        }
        return $info;
    }
}