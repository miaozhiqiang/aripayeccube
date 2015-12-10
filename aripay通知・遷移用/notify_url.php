<?php
require_once '../require.php';
require_once CLASS_REALDIR . '/aripay/alipay_notify.php';
require_once CLASS_REALDIR . '/helper/SC_Helper_Purchase.php';


$alipay = new alipay_notify(ARIPAY_PARTNER,ARIPAY_SECURITY_CODE,ARIPAY_SIGN_TYPE,ARIPAY_INPUT_CHARSET,ARIPAY_TRANSPORT);
$verify_result = $alipay->notify_verify();

 $objMail = new SC_Helper_Mail_Ex();

 
if($verify_result) {
	 $out_trade_no= $_POST["out_trade_no"];
	 $status = $_POST["trade_status"] ;
	 $trade_no =  $_POST["trade_no"];
	 $total_fee = $_POST["total_fee"];
	 $currency = $_POST["currency"];
	 
	 $objPurchase = new SC_Helper_Purchase();
	
	 $orderInfo = $objPurchase->getOrder($out_trade_no);
	 
	 if(is_null($orderInfo)){
			 log_result("�󒍔ԍ����݂��Ȃ��B�A���y�C�p�����[�^�o�� out_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
			 exit;
	 }
	$parameter = array(
				"memo01" => "�A���y�C�p�����[�^�o�� status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency
			);
	 
	 
  if($status=="TRADE_FINISHED" && $orderInfo['status'] != ORDER_DELIV && $orderInfo['status'] != ORDER_PRE_END){
		$objQuery =& SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();
		$objPurchase->sfUpdateOrderStatus($out_trade_no,ORDER_PRE_END);
		$objQuery->commit();
		log_result("�x���������B�A���y�C�p�����[�^�o�� out_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
 	
 		//�x�����������[�����M
        $objMail->sfSendOrderMail($out_trade_no, 7);
        
        
  
   }else if($status=="TRADE_CLOSED" && $orderInfo['status'] != ORDER_CANCEL){
   	    $objQuery =& SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();
	
		$objPurchase->sfUpdateOrderStatus($out_trade_no,ORDER_CANCEL);
		$objQuery->commit();
   	  log_result("�x�������߂��ł��B�󒍃L�����Z�����A�݌ɂ������߂��Iout_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
   }
}
else  {
	log_result ("�s���A�N�Z�X.");
}
function  log_result($word) {
	$fp = fopen("notify.log","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."��execution date ��".strftime("%Y%m%d%H%I%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
}
	
?>