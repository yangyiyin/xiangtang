<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughMakeQrcode extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

//    public function excute() {
//
//        $url = $this->post_data['url'];
//
//        $file_name = 'pages/qrcode/'.md5($url).'.png';
//        $link = 'https://www.88plus.net/public/'.$file_name;
//        if (file_exists($file_name)) {
//            return result_json(TRUE, '发布成功', $link);
//        }
//
//        require APP_PATH . '/Common/Lib/phpqrcode/qrlib.php';
//
//        $value = $url; //二维码内容
//
//        $errorCorrectionLevel = 'L';//容错级别
//
//        $matrixPointSize = 8;//生成图片大小
//
//        $tmp_png = time() . 'qrcode.png';
//
//        $logo = 'logo.png';//准备好的logo图片
//
//        //生成二维码图片
//
//        \QRcode::png($value, $tmp_png, $errorCorrectionLevel, $matrixPointSize, 2);
//
//
//        $QR = $tmp_png;//已经生成的原始二维码图
//
//
//
//        if ($logo !== FALSE) {
//
//            $QR = imagecreatefromstring(file_get_contents($QR));
//
//            $logo = imagecreatefromstring(file_get_contents($logo));
//
//            $QR_width = imagesx($QR);//二维码图片宽度
//
//            $QR_height = imagesy($QR);//二维码图片高度
//
//            $logo_width = imagesx($logo);//logo图片宽度
//
//            $logo_height = imagesy($logo);//logo图片高度
//
//            $logo_qr_width = $QR_width / 5;
//
//            $scale = $logo_width/$logo_qr_width;
//
//            $logo_qr_height = $logo_height/$scale;
//
//            $from_width = ($QR_width - $logo_qr_width) / 2;
//
//            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
//
//                $logo_qr_height, $logo_width, $logo_height);
//
//        }
//
//        imagepng($QR, $file_name);
//        unlink($tmp_png);
//        return result_json(TRUE, '发布成功',$link);
//    }
    public function excute(){
        $page_id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];

        $file_name = __ROOT__.'/pages/qrcode/'.md5($page_id.','.$extra_uid).'.png';
        //$file_path = __ROOT__.'/'.$file_name;

//        $link = 'https://www.88plus.net/public/'.$file_name;
        $link = 'http://paz3jxo1v.bkt.clouddn.com/'.md5($file_name);
        if (file_get_contents($link)) {
            return result_json(TRUE, '成功', $link);
        }

        //获取openid
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx979328bc70cabb2d&secret=d2e17f107d1204f6a6545662894040c0");
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx939ea03c3f8d5f12&secret=d792f5bb4265934e2d19e59c16620535");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $ret = json_decode($output,true);

        if (!$ret || !isset($ret['access_token'])) {
            return result_json(false, '网络繁忙,请稍后再试');
        }
        $access_token = $ret['access_token'];

        //https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN
        $scene = $page_id.'a'.$extra_uid;
//        var_dump($scene);
        $post_data = ['scene'=>urlencode($scene),'page'=>'pages/tmp_make/index'];
        $post_data = json_encode($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        //$ret = json_decode($output,true);
       // var_dump($output);die();
//        $file_name = 'pages/qrcode/'.md5($page_id.','.$extra_uid).'.png';
        $ret = file_put_contents($file_name, $output);
        if ($ret) {
            $return = $this->uploadPicture($file_name);
            if (!$return) {
                return result_json(false, '网络繁忙002,请稍后再试');
            }
        }
//        $link = 'https://www.88plus.net/public/'.$file_name;
        return result_json(TRUE, '成功', $link);
    }

    public function uploadPicture($file){

        $files = [];
        $files['file'] = new \CURLFile(realpath($file));
        $files['obj_name'] = md5($file);
        $ret = curl_post_form('http://api.88plus.net/index.php/waibao/common/qiniu_upload?bucket=onepixel-pub', $files);
        $ret = json_decode($ret, true);
        if ($ret && isset($ret['code']) && $ret['code'] == 100) {
            return $ret['data'];
        }

        return false;

    }



}