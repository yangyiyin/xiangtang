<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/5/13
 * Time: 下午3:08
 */
namespace Common\Service;
require APP_PATH . '/Common/Lib/aliyun_sms_sdk/mns-autoloader.php';
use AliyunMNS\Client;
use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

class AliyunMsmService extends BaseService
{

    private $endPoint = "http://10961015.mns.cn-hangzhou.aliyuncs.com"; // eg. http://1234567890123456.mns.cn-shenzhen.aliyuncs.com
    private $accessId = "LTAIR93Mh9svraqQ";
    private $accessKey = "6rGGuTezxkbeTMXbt8nXU8VMX85NTO";

    private $client;
    protected function init()
    {
        parent::init();
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
    }

    public function run($tel_num, $params)
    {
        /**
         * Step 2. 获取主题引用
         */
        $topicName = "sms.topic-cn-hangzhou";
        $topic = $this->client->getTopicRef($topicName);

        // 3.1 设置发送短信的签名（SMSSignName）和模板（SMSTemplateCode）
        $batchSmsAttributes = new BatchSmsAttributes("绿锦", "SMS_75775160");
        // 3.2 （如果在短信模板中定义了参数）指定短信模板中对应参数的值
        $batchSmsAttributes->addReceiver((string) $tel_num, $params);
        //$batchSmsAttributes->addReceiver("YourReceiverPhoneNumber2", array("YourSMSTemplateParamKey1" => "value1"));
        $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
        /**
         * Step 4. 设置SMS消息体（必须）
         *
         * 注：目前暂时不支持消息内容为空，需要指定消息内容，不为空即可。
         */
        $messageBody = "123";
        /**
         * Step 5. 发布SMS消息
         */
        $request = new PublishMessageRequest($messageBody, $messageAttributes);
        try
        {
            $res = $topic->publishMessage($request);
//            echo $res->isSucceed();
//            echo "\n";
//            echo $res->getMessageId();
           // E78C397592DF04D0-1-15C1589DFBD-200000002

//            echo "\n";
        }
        catch (MnsException $e)
        {
//            echo $e;
//            echo "\n";
        }
    }
}