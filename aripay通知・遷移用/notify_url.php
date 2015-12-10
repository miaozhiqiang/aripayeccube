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
			 log_result("受注番号存在しない。アリペイパラメータ出力 out_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
			 exit;
	 }
	$parameter = array(
				"memo01" => "アリペイパラメータ出力 status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency
			);
	 
	 
  if($status=="TRADE_FINISHED" && $orderInfo['status'] != ORDER_DELIV && $orderInfo['status'] != ORDER_PRE_END){
		$objQuery =& SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();
		$objPurchase->sfUpdateOrderStatus($out_trade_no,ORDER_PRE_END);
		$objQuery->commit();
		log_result("支払い完了。アリペイパラメータ出力 out_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
 	
 		//支払い完了メール送信
        $objMail->sfSendOrderMail($out_trade_no, 7);
        
        
  
   }else if($status=="TRADE_CLOSED" && $orderInfo['status'] != ORDER_CANCEL){
   	    $objQuery =& SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();
	
		$objPurchase->sfUpdateOrderStatus($out_trade_no,ORDER_CANCEL);
		$objQuery->commit();
   	  log_result("支払期限過ぎです。受注キャンセルし、在庫を引き戻す！out_trade_no:".$out_trade_no."status:".$status."trade_no:".$trade_no."total_fee:".$total_fee."currency".$currency); 
   }
}
else  {
	log_result ("不正アクセス.");
}
function  log_result($word) {
	$fp = fopen("notify.log","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."｣ｺexecution date ｣ｺ".strftime("%Y%m%d%H%I%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
}
	
?>