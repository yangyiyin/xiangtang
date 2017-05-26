<?php
	require_once(dirname(__FILE__)."/mqs.class.php");
	
	$Accessid='你的Accessid';
	$AccessKey='你的AccessKey';
	$queueownerid='你的queueownerid';
    
	$mqsurl='mqs-cn-hangzhou.aliyuncs.com';			//杭州的地址
	
	$mqs=new Message($Accessid,$AccessKey,$queueownerid,$mqsurl);
	
	$queueName='xybingbing';
	
	if(!isset($_GET['do'])) $_GET['do'] = 'get';
	
	if($_GET['do']=='get'){
	
		$message=$mqs->ReceiveMessage($queueName,10);		//接收消息列队里的消息
		if($message['state']=='ok'){
			echo $message['msg']['MessageBody'];
			$mqs->DeleteMessage($queueName,$message['msg']['ReceiptHandle']);		//删除刚刚接收的消息
			exit();
		}
		
	}elseif($_GET['do']=='pus'){
	
		if(!empty($_POST['content'])){
			$mqs->SendMessage($queueName,$_POST['content']);	//发送消息
		}
		
	}
    