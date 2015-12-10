<?php
require_once '../require.php';

require_once CLASS_REALDIR . '/aripay/alipay_service.php';

require_once CLASS_REALDIR . '/helper/SC_Helper_Purchase.php';

$link = "hogegoe";
$orderid = $_GET["id"];

if (!is_null($orderid))
{
	$objPurchase = new SC_Helper_Purchase();
	$orderInfo = $objPurchase->getOrder($orderid);
	if (!is_null($orderInfo))
	{
		// 7:決済処理中 ,1:新規受付,2:入金待ち
		if ($orderInfo["status"] == 7 || $orderInfo["status"] == 1 || $orderInfo["status"] == 2)
			{
			$parameter = array(
				"service" => "create_forex_trade", //service name
				"partner" => ARIPAY_PARTNER,
				"return_url" => "hogehoge",
				"notify_url" => ARIPAY_NOTIFY_URL_HTTP,
				"_input_charset" => ARIPAY_INPUT_CHARSET,
				"subject" => "会社", // 
				"body" => "おいしいケーキ", //説明
				"out_trade_no" => $orderid,
				"total_fee" => $orderInfo["total"], //金額
				"currency" => "JPY", // 単位
				//"timeout_rule" => "2h"
			);
			$alipay = new alipay_service($parameter, ARIPAY_SECURITY_CODE, ARIPAY_SIGN_TYPE);
			print_r($parameter);
			$link = $alipay->create_url();				
			log_result($orderid."アリペイURL：".$link);
//			echo $link;			
//			print <<<EOT
//<br/>
//<a href= $link  target= "_blank">submit</a>
//EOT;

			}
		}
	}
	header("Location: ".$link); 
function  log_result($word) {
	$fp = fopen("request_log.log","a");	
	flock($fp, LOCK_EX) ;
	fwrite($fp,$word."｣ｺexecution date ｣ｺ".strftime("%Y%m%d%H%I%S",time())."\t\n");
	flock($fp, LOCK_UN); 
	fclose($fp);
}

?>

