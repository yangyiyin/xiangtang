<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/5/13
 * Time: 下午3:08
 */
namespace Common\Service;
//require APP_PATH . '/Common/Lib/aliyun_sms_sdk/mns-autoloader.php';
//use AliyunMNS\Client;
//use AliyunMNS\Topic;
//use AliyunMNS\Constants;
//use AliyunMNS\Model\MailAttributes;
//use AliyunMNS\Model\SmsAttributes;
//use AliyunMNS\Model\BatchSmsAttributes;
//use AliyunMNS\Model\MessageAttributes;
//use AliyunMNS\Exception\MnsException;
//use AliyunMNS\Requests\PublishMessageRequest;
//
//class AliyunMsmService extends BaseService
//{
//
//    private $endPoint = "http://10961015.mns.cn-hangzhou.aliyuncs.com"; // eg. http://1234567890123456.mns.cn-shenzhen.aliyuncs.com
//    private $accessId = "LTAIR93Mh9svraqQ";
//    private $accessKey = "6rGGuTezxkbeTMXbt8nXU8VMX85NTO";
//
//    private $client;
//    protected function init()
//    {
//        parent::init();
//        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
//    }
//
//    public function run($tel_num, $params)
//    {
//        /**
//         * Step 2. 获取主题引用
//         */
//        $topicName = "sms.topic-cn-hangzhou";
//        $topic = $this->client->getTopicRef($topicName);
//
//        // 3.1 设置发送短信的签名（SMSSignName）和模板（SMSTemplateCode）
//        $batchSmsAttributes = new BatchSmsAttributes("火烧云", "SMS_85650011");
//        // 3.2 （如果在短信模板中定义了参数）指定短信模板中对应参数的值
//        $batchSmsAttributes->addReceiver((string) $tel_num, $params);
//        //$batchSmsAttributes->addReceiver("YourReceiverPhoneNumber2", array("YourSMSTemplateParamKey1" => "value1"));
//        $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
//        /**
//         * Step 4. 设置SMS消息体（必须）
//         *
//         * 注：目前暂时不支持消息内容为空，需要指定消息内容，不为空即可。
//         */
//        $messageBody = "123";
//        /**
//         * Step 5. 发布SMS消息
//         */
//        $request = new PublishMessageRequest($messageBody, $messageAttributes);
//        try
//        {
//            $res = $topic->publishMessage($request);
////            echo $res->isSucceed();
////            echo "\n";
////            echo $res->getMessageId();
//           // E78C397592DF04D0-1-15C1589DFBD-200000002
//
////            echo "\n";
//        }
//        catch (MnsException $e)
//        {
////            echo $e;
////            echo "\n";
//        }
//    }
//}

//ini_set("display_errors", "on");

require_once APP_PATH . '/Common/Lib/aliyun-dysms-php-sdk/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;

// 加载区域结点配置
Config::load();

/**
 * Class SmsDemo
 *
 * Created on 17/10/17.
 * 短信服务API产品的DEMO程序,工程中包含了一个SmsDemo类，直接通过
 * 执行此文件即可体验语音服务产品API功能(只需要将AK替换成开通了云通信-短信服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
class AliyunMsmService extends BaseService
{

    static $acsClient = null;

    protected function init(){

    }
    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public static function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = "LTAIR93Mh9svraqQ"; // AccessKeyId

        $accessKeySecret = "6rGGuTezxkbeTMXbt8nXU8VMX85NTO"; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public static function run($tel_num, $params) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers((string) $tel_num);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName("绿锦");

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode("SMS_75775160");

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($params, JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
//        $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
//        $request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);
        //var_dump($acsResponse);
        return $acsResponse;

    }

//    /**
//     * 短信发送记录查询
//     * @return stdClass
//     */
//    public static function querySendDetails() {
//
//        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
//        $request = new QuerySendDetailsRequest();
//
//        // 必填，短信接收号码
//        $request->setPhoneNumber("12345678901");
//
//        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
//        $request->setSendDate("20170718");
//
//        // 必填，分页大小
//        $request->setPageSize(10);
//
//        // 必填，当前页码
//        $request->setCurrentPage(1);
//
//        // 选填，短信发送流水号
//        $request->setBizId("yourBizId");
//
//        // 发起访问请求
//        $acsResponse = static::getAcsClient()->getAcsResponse($request);
//
//        return $acsResponse;
//    }

}