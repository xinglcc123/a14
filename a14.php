<?php

namespace App\Http\Controllers;

require_once __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

// require 'vendor/autoload.php';
// use guzzle\src\Client;
// use GuzzleHttp\Client;

// use App\Http\Controllers\a14Controller;

$a14 = new a14Controller();
$a14->a14();
// $a14->img_gray('validCode.jfif');
// $test = $a14->captcha_Ocr('validCode.jfif');
// echo var_dump($test);

class a14Controller
{

	public $cookie = 'urlColor=agent%2FAccountServlet%3Frate%3D5%26bool%3D5; unknown=7AEAF279E042E64B494C9EBA5CA991B5; route=e2a13162f21a4fbb48f22bb227936975; JSESSIONID=9BEA32CE7B5F36E48582CAC9FAEED921';
	public $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36';

	function __construct()
	{
		$this->domData = new \DOMDocument();
	}

	public function a14()
	{

		// $this->img_gray('validCode.jfif');
		// $test = $this->captcha_Ocr('validCode.jfif');
		// echo $test;
		// exit;
		$rate = 5;	//抓取類別
		$pageNo = 1;	//起始頁數1開始
		$pageSize = 230;	//每頁抓取會員數
		$moneySize = 50;	//每次查詢會員錢包人數
		$muchSize = 8;	//多線程數量

		//用抓的
		// $getMemberData = $this->getMemberData($rate, $pageNo, $pageSize);
		// $htmlData = $getMemberData->getBody()->getContents();
		// exit;

		//使用excel
		$fp = fopen('file' . date("YmdHis") . '.csv', 'a+');
		fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); //解決excel寫入亂碼

		$idMiss = [];
		$t0 = microtime(true);	//返回当前时间戳和微秒数
		$t3 = 0;
		$i = 0;
		do {
			$t1 = microtime(true);
			//用curl
			$htmlData = $this->curlMemberData($rate, $pageNo + $i, $pageSize);
			//處理回傳的HTML
			$arrMemberData = $this->MemberData($htmlData);
			$arrId = $arrMemberData['id'];
			unset($arrMemberData['id']);
			//查詢會員餘額&會員詳細資料
			$i2 = 0;
			$i2count = count($arrId) / $moneySize;
			do {
				$tt3 = $t3;
				$tt1 = microtime(true);
				$quantityQuery = array_slice($arrId, $i2 * $moneySize, $moneySize);	//陣列, 每次起始位置, 每次查詢數量
				$arrMoneyAll = $this->getAmount3($quantityQuery, $muchSize);	//查詢會員餘額
				if (isset($arrMoneyAll['success'])) {	//查詢會員餘額失敗的會員
					$idMiss['arrMoneyAll'][] = $arrMoneyAll['idMiss'];
					unset($arrMoneyAll['idMiss']);	//函式從 PHP 陣列中刪除一個元素
				}
				$getMemberDataAll = $this->getMemberDataAll2($quantityQuery, $muchSize);	//查詢會員詳細資料
				if (isset($getMemberDataAll['success'])) {	//查詢會員詳細資料失敗的會員
					$idMiss['getMemberDataAll'][] = $getMemberDataAll['idMiss'];
					unset($getMemberDataAll['idMiss']);	//函式從 PHP 陣列中刪除一個元素
				}
				//放入會員陣列
				foreach ($quantityQuery as $value) {
					$arrMemberData[$value] = array_merge($arrMemberData[$value], $arrMoneyAll[$value], $getMemberDataAll[$value]);	//寫入會員資料陣列
					// $arrMemberData[$value] = array_merge($arrMemberData[$value], $arrMoneyAll[$value]);	//寫入會員資料陣列
				}
				$tt2 = microtime(true);
				$tt3 += round($tt2 - $tt1, 3);
				echo '線程' . $muchSize . '會員餘額' . $pageNo + $i2 . '筆數' . count($quantityQuery) . '耗時' . round($tt2 - $tt1, 3) . '秒，總耗時' . $tt3 . PHP_EOL . '</br>';
				$i2++;
				// } while (false);
			} while ($i2 < $i2count);	//檢查是否最後一組查詢
			//excel寫入會員資料
			// echo json_encode($arrMemberData, JSON_UNESCAPED_UNICODE);
			// exit;
			fputcsv($fp, ['代號','用户名','中文昵称','真实姓名','代理','总代','股东','大股东','账户余额','推广会员','账号','投注','资金状态','审核','注册时间','最近登录时间','登录异常次数','登录','资金状态','总余额','现金余额','传统彩票余额','BB余额','AG余额','沙巴体育余','PT余额','MWG余额','LEBO余额','DS余额','AB余额','PP余额','CMD余额','VG余额','CQ9余额','OG体育余额-','BG余额','JDB余额','FG余额','KY余额','性感百家乐余额','BSP余额','EBET余额','SG余额','PG余额','新MG余额','TY余额','新OG余额','天能余额','微信昵称','英文昵称','生日','国家','身份证号','护照号码','手机','微信','QQ','E-mail','支付宝','取款密码','登录密码','备注','动态口令(操作人)','卡的數量','取款银行','开户行省','开户行市','其他市县','开户行','取款账号','默认银行卡','取款银行','开户行省','开户行市','其他市县','开户行','取款账号','默认银行卡','取款银行','开户行省','开户行市','其他市县','开户行','取款账号','默认银行卡']);
			foreach ($arrMemberData as $value) {
				// echo var_dump($arrMemberData) . PHP_EOL . '</br>';
				// exit;
				fputcsv($fp, $value);
			}
			$t2 = microtime(true);
			$t3 = round($t2 - $t0, 3);
			echo '會員' . $pageNo + $i . '筆數' . count($arrMemberData) . '耗時' . round($t2 - $t1, 3) . '秒，總耗時' . $t3 . PHP_EOL . '</br>';
			$i++;
		} while (false);
		// } while (count($arrMemberData) == $pageSize);	//檢查是否最後一頁
		fclose($fp);
		//漏掉的寫入筆記本
        $jsonIdMiss = json_encode($idMiss, JSON_UNESCAPED_UNICODE);
        file_put_contents("data" . date("YmdHis") . ".txt", $jsonIdMiss);
		exit;
	}

	public function captcha_Ocr($path)
	{
		$path_tesseract = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
		return (new TesseractOCR($path))
			->executable($path_tesseract) //預設路徑有誤 重新配置
			->run();
	}

	public function getMemberDataAll2($id, $muchSize)	/*抓取會員詳細資料($id 抓取的會員資料陣列, $muchSize 線程)*/
	{
		$idMiss = [];
		$result = [];
		$times = 0;
		do {
			$id = (count($idMiss) == 0) ? $id : $idMiss;
			//定义目标URL列表
			$urls = [];
			foreach ($id as $value) {
				$urls[$value] = 'https://arxntfea14.sharksu.com/agent/AccountServlet?doQueryMessageInfo=queryMessageInfo&rate=5&bool=5&id=' . $value . '&banktype=0&pageLog=/agent/AccountServlet?rate=5&bool=5';
			}
			// echo json_encode($urls);
			// exit;

			//由于$urls数据量较大，使用array_chunk函数分割
			$urlChucks = array_chunk($urls, $muchSize, true);
			// echo json_encode($urlChucks);
			// exit;

			$mh = curl_multi_init(); //获取新cURL批处理句柄
			foreach ($urlChucks as $key => $url) {
				$ch = [];
				$i = 0;
				foreach ($url as $key => $value) {
					// echo json_encode($url[$key]);
					// echo json_encode($url);
					// exit;
					$ch[] = curl_init();
					curl_setopt($ch[$i], CURLOPT_PROXY, '10.0.0.48:800' . ($i + 1));
					curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
						'Cookie:' . $this->cookie,
						'User-Agent:  ' . $this->userAgent
					));
					curl_setopt($ch[$i], CURLOPT_URL, $url[$key]);
					curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1); //返回结果，不输出在页面上
					curl_multi_add_handle($mh, $ch[$i]);             //将生成的单个curl句柄，加入到$mh中
					$i++;
				}

				//执行此处汇总的curl
				do {
					curl_multi_exec($mh, $running);
					// echo json_encode($mh);
					// exit;
					// $arrMemberDataAll[$value] = $this->MemberDataAll($result);
				} while ($running > 0);

				$i = 0;
				foreach ($url as $key => $value) {
					// if ($i != 3) {
					# code...
					// echo json_encode($value);
					// exit;
					//获取api返回结果
					$result[$key] = $this->MemberDataAll(curl_multi_getcontent($ch[$i]));	//處理回傳的HTML
					curl_multi_remove_handle($mh, $ch[$i]); //移除ch句柄
					// echo var_dump($result[$key]) . PHP_EOL . '</br></br>';
					// exit;
					// echo var_dump($ch[$i]) . PHP_EOL . '</br></br>';
					$idCheck[] = $key;
					// }
					$i++;
				}
				unset($url);
				// exit;
				// $arrMemberDataAll[$value] = $this->MemberDataAll($result);
			}
			curl_multi_close($mh);

			$idMiss = array_diff($id, $idCheck);	//取出抓取失敗會員
			if (count($idMiss) != 0) {
				$result['idMiss'] = $idMiss;
				// echo json_encode($idMiss) . PHP_EOL . '</br></br>';
				$times++;
			}
			// echo '撈取失敗會員' . json_encode($idMiss) . '</br>';
			// exit;
			// } while (false);	//檢查是否有遺漏
		} while ($times < 5 && count($idMiss) != 0);	//檢查是否有遺漏
		// exit;

		// $curl = curl_init();
		// //用proxy
		// $proxy = "10.0.0.48:8001";
		// curl_setopt($curl, CURLOPT_PROXY, $proxy);

		// foreach ($id as $value) {
		// 	curl_setopt_array($curl, array(
		// 		CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/AccountServlet?doQueryMessageInfo=queryMessageInfo&rate=5&bool=5&id=' . $value . '&banktype=0&pageLog=/agent/AccountServlet?rate=5&bool=5',
		// 		// CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/AccountServlet?doQueryMessageInfo=queryMessageInfo&rate=5&bool=5&id=11000286&banktype=0&pageLog=/agent/AccountServlet?rate=5&bool=5',
		// 		CURLOPT_RETURNTRANSFER => true,
		// 		CURLOPT_ENCODING => '',
		// 		CURLOPT_MAXREDIRS => 10,
		// 		CURLOPT_TIMEOUT => 0,
		// 		CURLOPT_FOLLOWLOCATION => true,
		// 		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		// 		CURLOPT_CUSTOMREQUEST => 'POST',
		// 		CURLOPT_HTTPHEADER => array(
		// 			'Cookie:' . $this->cookie,
		// 			'User-Agent:  ' . $this->userAgent
		// 		),
		// 	));

		// 	$response = curl_exec($curl);
		// 	$arrMemberDataAll[$value] = $this->MemberDataAll($response);
		// }

		// curl_close($curl);
		return $result;
	}

	public function getMemberDataAll($id)	/*抓取會員詳細資料($id 抓取的會員資料陣列)*/
	{
		$curl = curl_init();
		//用proxy
		$proxy = "10.0.0.48:8001";
		curl_setopt($curl, CURLOPT_PROXY, $proxy);

		foreach ($id as $value) {
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/AccountServlet?doQueryMessageInfo=queryMessageInfo&rate=5&bool=5&id=' . $value . '&banktype=0&pageLog=/agent/AccountServlet?rate=5&bool=5',
				// CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/AccountServlet?doQueryMessageInfo=queryMessageInfo&rate=5&bool=5&id=11000286&banktype=0&pageLog=/agent/AccountServlet?rate=5&bool=5',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Cookie:' . $this->cookie,
					'User-Agent:  ' . $this->userAgent
				),
			));

			$response = curl_exec($curl);
			$arrMemberDataAll[$value] = $this->MemberDataAll($response);
		}

		curl_close($curl);
		return $arrMemberDataAll;
	}

	public function MemberDataAll($htmlData)	/*整理會員詳細資料HTML資料轉為陣列($htmlData 抓取的HTML資料)*/
	{
		@$this->domData->loadHTML($htmlData);
		$dataTableTrs = $this->domData->getElementsByTagName("tbody")->item(1)->getElementsByTagName("tr"); //取所有會員詳細資料tr
		$dataTable2Trs = $this->domData->getElementsByTagName("tbody")->item(2)->getElementsByTagName("tr"); //取所有銀行tr
		$cardQuantity = $dataTableTrs->item(12)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value');	//卡的數量
		$arrMemberData = [
			// $dataTableTrs->item(0)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//真实姓名
			$dataTableTrs->item(1)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//微信昵称
			// $dataTableTrs->item(2)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//中文昵称
			$dataTableTrs->item(3)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//英文昵称
			$dataTableTrs->item(4)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//生日
			$dataTableTrs->item(5)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//国家
			$dataTableTrs->item(6)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//身份证号
			$dataTableTrs->item(7)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//护照号码
			$dataTableTrs->item(8)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//手机
			$dataTableTrs->item(9)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//微信
			$dataTableTrs->item(10)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//QQ
			$dataTableTrs->item(11)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//E-mail
			$dataTableTrs->item(13)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//支付宝
			$dataTableTrs->item(14)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//取款密码
			$dataTableTrs->item(15)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//登录密码
			$dataTableTrs->item(16)->getElementsByTagName("td")->item(0)->getElementsByTagName("textarea")->item(0)->nodeValue,	//备注
			$dataTableTrs->item(17)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'),	//动态口令(操作人)
			$cardQuantity,	//卡的數量
		];
		for ($i = 1; $i <= $cardQuantity; $i++) {
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(0)->getElementsByTagName("input")->item(1)->getAttribute('value');	//取款银行
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(1)->getElementsByTagName("select")->item(0)->getAttribute('initval');	//开户行省
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(2)->getElementsByTagName("select")->item(0)->getAttribute('initval');	//开户行市
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(3)->getElementsByTagName("input")->item(0)->getAttribute('value');	//其他市县
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(4)->getElementsByTagName("input")->item(0)->getAttribute('value');	//开户行
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(5)->getElementsByTagName("input")->item(0)->getAttribute('value');	//取款账号
			$arrMemberData[] = $dataTable2Trs->item($i)->getElementsByTagName("td")->item(6)->getElementsByTagName("input")->item(0)->getAttribute('value');	//默认银行卡
		}
		// echo json_encode($arrMemberData) . PHP_EOL . '</br>';
		// exit;
		return $arrMemberData;
	}

	public function getAmount($id)	/*抓取會員所有餘額明細($id 抓取的會員資料陣列*/
	{
		$curl = curl_init();
		//用proxy
		$proxy = "10.0.0.48:8001";
		curl_setopt($curl, CURLOPT_PROXY, $proxy);

		$arrMoneyAll = [];
		foreach ($id as $value) {
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $value . '&apiKey=all&useApi=1',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Cookie:' . $this->cookie,
					'User-Agent:  ' . $this->userAgent
				),
			));

			$response = json_decode(curl_exec($curl), true);
			// echo $value;
			// echo json_encode($response);
			// exit;
			//整理回傳值
			$arrMoneyAll[$value] = [
				$response['success'],	//资金状态
				$response['totalMoney'],	//总余额
				$response['money'],	//现金余额
				$response['apiMoney']['cp'],	//传统彩票余额
				$response['apiMoney']['bb'],	//BB余额
				$response['apiMoney']['ag'],	//AG余额
				$response['apiMoney']['ibc'],	//沙巴体育余
				$response['apiMoney']['pt'],	//PT余额
				$response['apiMoney']['mwg'],	//MWG余额
				$response['apiMoney']['lebo'],	//LEBO余额
				$response['apiMoney']['ds'],	//DS余额
				$response['apiMoney']['ab'],	//AB余额
				$response['apiMoney']['pp'],	//PP余额
				$response['apiMoney']['cmd'],	//CMD余额
				$response['apiMoney']['vg'],	//VG余额
				$response['apiMoney']['cq9'],	//CQ9余额
				$response['apiMoney']['bc'],	//OG体育余额-
				$response['apiMoney']['bg'],	//BG余额
				$response['apiMoney']['jdb'],	//JDB余额
				$response['apiMoney']['fg'],	//FG余额
				$response['apiMoney']['ky'],	//KY余额
				$response['apiMoney']['sc'],	//性感百家乐余额
				$response['apiMoney']['bsp'],	//BSP余额
				$response['apiMoney']['ebet'],	//EBET余额
				$response['apiMoney']['sg'],	//SG余额
				$response['apiMoney']['pg'],	//PG余额
				$response['apiMoney']['mgp'],	//新MG余额
				$response['apiMoney']['th'],	//TY余额
				$response['apiMoney']['ogp'],	//新OG余额
				$response['apiMoney']['tn'],	//天能余额
			];
		}

		curl_close($curl);
		// print_r($arrMoneyAll);
		// echo json_encode($arrMoneyAll);
		// exit;
		return $arrMoneyAll;
	}

	public function getAmount3($id, $muchSize)	/*抓取會員所有餘額明細($id 抓取的會員資料陣列, $muchSize 線程)*/
	{
		// echo json_encode($list);
		// exit;


		// echo json_encode($urlChucks);
		// exit;
		$arrMoneyAll = [];	//放回傳值
		$idMiss = [];	//所有漏掉的會員
		$times = 0;
		$idCheck = [];
		do {
			$id = (count($idMiss) == 0) ? $id : $idMiss;
			//定义目标URL列表
			$urls = [];
			foreach ($id as $value) {
				$urls[$value] = 'https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $value . '&apiKey=all&useApi=1';
			}
			//由于$urls数据量较大，使用array_chunk函数分割
			$urlChucks = array_chunk($urls, $muchSize, true);
			$mh = curl_multi_init(); //获取新cURL批处理句柄
			foreach ($urlChucks as $key => $url) {
				$ch = [];
				$i = 0;
				foreach ($url as $key => $value) {
					// echo json_encode($url[$key]);
					// echo json_encode($url);
					// exit;
					$ch[] = curl_init();
					curl_setopt($ch[$i], CURLOPT_PROXY, '10.0.0.48:800' . ($i + 1));
					curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
						'Cookie:' . $this->cookie,
						'User-Agent:  ' . $this->userAgent
					));
					curl_setopt($ch[$i], CURLOPT_URL, $url[$key]);
					curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1); //返回结果，不输出在页面上
					curl_multi_add_handle($mh, $ch[$i]);             //将生成的单个curl句柄，加入到$mh中
					$i++;
				}

				//执行此处汇总的curl
				do {
					curl_multi_exec($mh, $running);
				} while ($running > 0);

				$i = 0;
				foreach ($url as $key => $value) {
					// echo $key . PHP_EOL . '</br></br>';
					$result = json_decode(curl_multi_getcontent($ch[$i]), true);	//获取api返回结果
					curl_multi_remove_handle($mh, $ch[$i]);	//移除ch句柄
					// if (isset($result['success']) && $i != 0) {	//模擬查詢失敗
					if (isset($result['success'])) {
						$idCheck[] = $key;
						//整理回傳值
						$arrMoneyAll[$key] = [
							$result['success'],	//资金状态
							$result['totalMoney'],	//总余额
							$result['money'],	//现金余额
							$result['apiMoney']['cp'],	//传统彩票余额
							$result['apiMoney']['bb'],	//BB余额
							$result['apiMoney']['ag'],	//AG余额
							$result['apiMoney']['ibc'],	//沙巴体育余
							$result['apiMoney']['pt'],	//PT余额
							$result['apiMoney']['mwg'],	//MWG余额
							$result['apiMoney']['lebo'],	//LEBO余额
							$result['apiMoney']['ds'],	//DS余额
							$result['apiMoney']['ab'],	//AB余额
							$result['apiMoney']['pp'],	//PP余额
							$result['apiMoney']['cmd'],	//CMD余额
							$result['apiMoney']['vg'],	//VG余额
							$result['apiMoney']['cq9'],	//CQ9余额
							$result['apiMoney']['bc'],	//OG体育余额-
							$result['apiMoney']['bg'],	//BG余额
							$result['apiMoney']['jdb'],	//JDB余额
							$result['apiMoney']['fg'],	//FG余额
							$result['apiMoney']['ky'],	//KY余额
							$result['apiMoney']['sc'],	//性感百家乐余额
							$result['apiMoney']['bsp'],	//BSP余额
							$result['apiMoney']['ebet'],	//EBET余额
							$result['apiMoney']['sg'],	//SG余额
							$result['apiMoney']['pg'],	//PG余额
							$result['apiMoney']['mgp'],	//新MG余额
							$result['apiMoney']['th'],	//TY余额
							$result['apiMoney']['ogp'],	//新OG余额
							$result['apiMoney']['tn'],	//天能余额
						];
						// echo $i . PHP_EOL . '</br></br>';
					} else {	//查詢失敗
						$arrMoneyAll[$key] = [
							'-',	//资金状态
							'-',	//总余额
							'-',	//现金余额
							'-',	//传统彩票余额
							'-',	//BB余额
							'-',	//AG余额
							'-',	//沙巴体育余
							'-',	//PT余额
							'-',	//MWG余额
							'-',	//LEBO余额
							'-',	//DS余额
							'-',	//AB余额
							'-',	//PP余额
							'-',	//CMD余额
							'-',	//VG余额
							'-',	//CQ9余额
							'-',	//OG体育余额-
							'-',	//BG余额
							'-',	//JDB余额
							'-',	//FG余额
							'-',	//KY余额
							'-',	//性感百家乐余额
							'-',	//BSP余额
							'-',	//EBET余额
							'-',	//SG余额
							'-',	//PG余额
							'-',	//新MG余额
							'-',	//TY余额
							'-',	//新OG余额
							'-',	//天能余额
						];
						echo $key . PHP_EOL . '</br>' . $times . '</br>';
					}
					$i++;
				}
				unset($url);
			}
			$idMiss = array_diff($id, $idCheck);	//取出抓取失敗會員
			if (count($idMiss) != 0) {
				$arrMoneyAll['idMiss'] = $idMiss;
				// echo json_encode($idMiss) . PHP_EOL . '</br></br>';
				$times++;
			}
		} while ($times < 5 && count($idMiss) != 0);	//檢查是否有遺漏
		// echo var_dump($id) . '</br></br>';
		// echo var_dump($idCheck) . '</br></br>';
		// $idMiss = array_diff($id, $idCheck);
		// echo var_dump($idMiss) . '</br></br>';
		// exit;

		curl_multi_close($mh);

		//打印返回结果
		// foreach ($result as $key => $value) {
		// 	echo $key . json_encode($value) . PHP_EOL . '</br></br>';
		// }
		// exit;
		// echo json_encode($result);

		//整理回傳值
		// echo json_encode($arrMoneyAll);
		// exit;
		return $arrMoneyAll;
	}

	public function getAmount2($id, $muchSize)	/*抓取會員所有餘額明細($id 抓取的會員資料陣列, $muchSize 線程)*/
	{

		$mh = curl_multi_init();
		$charr = [];
		// foreach ($id as $key => $value) {
		// 	// 初始化异步多请求
		// 	$ch1 = curl_init('https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $value . '&apiKey=all&useApi=1');
		// 	//用proxy
		// 	$proxy = "10.0.0.48:8001";
		// 	curl_setopt($ch1, CURLOPT_PROXY, $proxy);
		// 	curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
		// 		'Cookie:' . $this->cookie,
		// 		'User-Agent:  ' . $this->userAgent
		// 	));
		// 	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
		// 	// 添加前面的每个handle
		// 	curl_multi_add_handle($mh, $ch1);
		// 	$ch1arr[$value] = $ch1;
		// }
		$size = count($id) / $muchSize;
		for ($i = 0; $i < $size; $i++) {
			$quantityQuery = array_slice($id, $i * $muchSize, $muchSize);	//陣列, 每次起始位置, 每次查詢數量
			// echo json_encode($ch1arr) . PHP_EOL . '</br>';
			// 同样先初始化curl
			$j = 1;
			foreach ($quantityQuery as $key => $value) {
				// echo ($value) . PHP_EOL . '</br>';
				$ch[$j] = curl_init('https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $value . '&apiKey=all&useApi=1');
				${"proxy" . $j} = "10.0.0.48:800" . $j;
				curl_setopt($ch[$j], CURLOPT_PROXY, ${"proxy" . $j});
				curl_setopt($ch[$j], CURLOPT_HTTPHEADER, array(
					'Cookie:' . $this->cookie,
					'User-Agent:  ' . $this->userAgent
				));
				// curl_setopt($ch[$j], CURLOPT_HEADER, 0);
				curl_setopt($ch[$j], CURLOPT_RETURNTRANSFER, 1);
				curl_multi_add_handle($mh, $ch[$j]);
				$charr[$j][$value] = $ch[$j];
				$j++;
				// echo json_encode($j) . PHP_EOL . '</br>';
				// echo json_encode($ch2) . PHP_EOL . '</br>';
			}
			// $ch1 = curl_init('https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $quantityQuery[0] . '&apiKey=all&useApi=1');
			// $ch2 = curl_init('https://arxntfea14.sharksu.com/agent/getAccountMoney?account6=' . $quantityQuery[1] . '&apiKey=all&useApi=1');
			// //用proxy
			// $proxy1 = "10.0.0.48:8001";
			// $proxy2 = "10.0.0.48:8002";
			// curl_setopt($ch1, CURLOPT_PROXY, $proxy1);
			// curl_setopt($ch2, CURLOPT_PROXY, $proxy2);
			// curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
			// 	'Cookie:' . $this->cookie,
			// 	'User-Agent:  ' . $this->userAgent
			// ));
			// curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
			// 	'Cookie:' . $this->cookie,
			// 	'User-Agent:  ' . $this->userAgent
			// ));
			// curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
			// // 添加前面的每个handle
			// curl_multi_add_handle($mh, $ch1);
			// curl_multi_add_handle($mh, $ch2);
			// $ch1arr[$quantityQuery[0]] = $ch1;
			// $ch2arr[$quantityQuery[1]] = $ch2;
		}
		// echo json_encode($charr1) . PHP_EOL . '</br>';
		// echo json_encode($charr2) . PHP_EOL . '</br>';
		// exit;

		// 执行请求
		$active = null;
		do {
			$status = curl_multi_exec($mh, $active);
		} while ($status === CURLM_CALL_MULTI_PERFORM);
		while ($active && $status == CURLM_OK) {
			if (curl_multi_select($mh) === -1) {
				usleep(100);
			}
			do {
				$status = curl_multi_exec($mh, $active);
			} while ($status === CURLM_CALL_MULTI_PERFORM);
		}

		// 如果需要返回结果
		for ($i = 1; $i <= count($charr); $i++) {
			foreach ($charr[$i] as $key => $value) {
				$res[$key] = json_decode(curl_multi_getcontent($value), true);
				// echo json_encode($key) . PHP_EOL . '</br>';
				// echo json_encode($value) . PHP_EOL . '</br>';
			}
			// 移除handle
			curl_multi_remove_handle($mh, $ch[$i]);
		}
		// exit;
		// foreach ($ch1arr as $key => $value) {
		// 	$res[$key] = json_decode(curl_multi_getcontent($value), true);
		// }

		// 移除handle
		// curl_multi_remove_handle($mh, $ch1);
		// curl_multi_remove_handle($mh, $ch2);

		// 关闭
		curl_multi_close($mh);

		foreach ($res as $key => $value) {
			echo $key . json_encode($value) . PHP_EOL . '</br></br>';
		}

		//整理回傳值
		$arrMoneyAll = [];
		foreach ($res as $key => $value) {
			//整理回傳值
			$arrMoneyAll[$key] = [
				$value['success'],	//资金状态
				$value['totalMoney'],	//总余额
				$value['money'],	//现金余额
				$value['apiMoney']['cp'],	//传统彩票余额
				$value['apiMoney']['bb'],	//BB余额
				$value['apiMoney']['ag'],	//AG余额
				$value['apiMoney']['ibc'],	//沙巴体育余
				$value['apiMoney']['pt'],	//PT余额
				$value['apiMoney']['mwg'],	//MWG余额
				$value['apiMoney']['lebo'],	//LEBO余额
				$value['apiMoney']['ds'],	//DS余额
				$value['apiMoney']['ab'],	//AB余额
				$value['apiMoney']['pp'],	//PP余额
				$value['apiMoney']['cmd'],	//CMD余额
				$value['apiMoney']['vg'],	//VG余额
				$value['apiMoney']['cq9'],	//CQ9余额
				$value['apiMoney']['bc'],	//OG体育余额-
				$value['apiMoney']['bg'],	//BG余额
				$value['apiMoney']['jdb'],	//JDB余额
				$value['apiMoney']['fg'],	//FG余额
				$value['apiMoney']['ky'],	//KY余额
				$value['apiMoney']['sc'],	//性感百家乐余额
				$value['apiMoney']['bsp'],	//BSP余额
				$value['apiMoney']['ebet'],	//EBET余额
				$value['apiMoney']['sg'],	//SG余额
				$value['apiMoney']['pg'],	//PG余额
				$value['apiMoney']['mgp'],	//新MG余额
				$value['apiMoney']['th'],	//TY余额
				$value['apiMoney']['ogp'],	//新OG余额
				$value['apiMoney']['tn'],	//天能余额
			];
		}
		// echo json_encode($arrMoneyAll);
		// exit;
		return $arrMoneyAll;
	}

	public function MemberData($htmlData)	/*整理會員詳細資料HTML資料轉為陣列($htmlData 抓取的HTML資料)*/
	{
		// $domData = new \DOMDocument();
		@$this->domData->loadHTML($htmlData);
		$arrMemberData = [];
		$trs = $this->domData->getElementsByTagName("tr");
		$size = count($trs) - 1;
		for ($i = 1; $i < $size; $i += 5) {	//會員資料在5*n+1的位置
			$tds = $trs->item($i)->getElementsByTagName("td");	//取所有tr
			$id = ($tds->item(0)->getElementsByTagName("input")->item(0)->getAttribute('value'));
			$arrMemberData['id'][] = $id;	//代號, id, 搜尋會員詳細資料&詳細餘額
			$arrMemberData[$id] = [
				$id,	//代號, id
				trim($tds->item(2)->nodeValue),	//用户名, name
				$tds->item(3)->nodeValue,	//中文昵称名, nickName
				$tds->item(4)->nodeValue,	//真实姓名, trueName
				$tds->item(5)->nodeValue,	//代理, acting
				$tds->item(6)->nodeValue,	//总代, totalGeneration
				$tds->item(7)->nodeValue,	//股东, shareholders
				$tds->item(8)->nodeValue,	//大股东, majorShareholder
				$tds->item(9)->getElementsByTagName("td")->item(0)->nodeValue,	//账户余额, accountBalance
				trim($tds->item(97)->nodeValue),	//推广会员, promotionMember
				trim($tds->item(98)->nodeValue),	//账号, account
				trim($tds->item(99)->nodeValue),	//投注, betting
				trim($tds->item(100)->nodeValue),	//资金状态, fundStatus
				$tds->item(101)->nodeValue,	//审核, audit
				$tds->item(102)->nodeValue,	//注册时间, registrationTime
				$tds->item(103)->nodeValue,	//最近登录时间, lastLoginTime
				$tds->item(104)->nodeValue,	//登录异常次数, numberOfAbnormalLogins
				trim($tds->item(105)->nodeValue),	//登录, logOn
			];
		}
		// echo json_encode($arrMemberData) . '</br>';
		// exit;
		return $arrMemberData;
	}

	public function curlMemberData($rate, $pageNo, $pageSize)	/*curl抓取會員資料，回傳HTML資料($rate 抓取類別, $pageNo 起始頁數1開始, $pageSize 每次抓取會員數)*/
	{
		$curl = curl_init();
		//用proxy
		$proxy = "10.0.0.48:8001";
		curl_setopt($curl, CURLOPT_PROXY, $proxy);

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://arxntfea14.sharksu.com/agent/AccountServlet?rate=' . $rate . '&pageNo=' . $pageNo . '&pageSize=' . $pageSize,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => array(
				'Cookie:  ' . $this->cookie,
				'User-Agent:  ' . $this->userAgent
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public function getMemberData($rate, $pageNo, $pageSize)	/*抓取會員資料，回傳HTML資料($rate 抓取類別, $pageNo 起始頁數1開始, $pageSize 每次抓取會員數)*/
	{
		$client = new Client();

		//捞资料
		$response = $client->request('POST', 'https://arxntfea14.sharksu.com/agent/AccountServlet', [
			'headers' => [
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
				'Accept-Encoding' => 'gzip, deflate, br',
				'Accept-Language' => 'zh-TW,zh-CN;q=0.9,zh;q=0.8,en-US;q=0.7,en;q=0.6,und;q=0.5,ja;q=0.4',
				'Cache-Control' => 'max-age=0',
				'Connection' => 'keep-alive',
				'Content-Length' => '219',
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Cookie' => $this->cookie,
				'Host' => 'arxntfea14.sharksu.com',
				'Origin' => 'https://arxntfea14.sharksu.com',
				'Referer' => 'https://arxntfea14.sharksu.com/agent/AccountServlet',
				'sec-ch-ua' => '"Chromium";v="92", " Not A;Brand";v="99", "Google Chrome";v="92"',
				'sec-ch-ua-mobile' => '?0',
				'Sec-Fetch-Dest' => 'frame',
				'Sec-Fetch-Mode' => 'navigate',
				'Sec-Fetch-Site' => 'same-origin',
				'Sec-Fetch-User' => '?1',
				'Upgrade-Insecure-Requests' => '1',
				'User-Agent' => $this->userAgent,
			],
			'form_params' => [
				'order' => 'create_time',
				'type' => '1',
				'id' => '',
				'rate' => '5',
				// 'bool' => '5',
				'status' => '',
				// 'pageNo' => '1',
				// 'pageSize' => '20',
				'accountIds' => '',
				'startTime' => '',
				'endTime' => '',
				'memberType' => '0',
				'promotionStatus' => '',
				'moneyStatus' => '2',
				'betStatus' => '-1',
				'errorNum' => '8',
				'multiplekey' => '1',
				'queryType' => '1',
				'accounts' => '',
				// 'pageSize' => '20',
			],
			'query' => [
				'rate' => $rate,
				// 'bool' => $bool,
				'pageNo' => $pageNo,
				'pageSize' => $pageSize,
			]
		]);

		return $response;
	}

	public function sql()
	{

		//SQL
		// $con = mysqli_connect('127.0.0.1', 'root', '', 'a14');

		// // Check connection
		// if (mysqli_connect_errno())
		// {
		// echo "Failed to connect to MySQL: " . mysqli_connect_error();
		// }

		// $sql="SELECT * FROM tmp_a14_account";

		// var_dump($sql);

		// exit();

		// if ($result=mysqli_query($con,$sql))
		// {
		// // Seek to row number 15
		// mysqli_data_seek($result,14);

		// // Fetch row
		// $row=mysqli_fetch_row($result);

		// printf ("Lastname: %s Age: %s\n", $row[0], $row[1]);

		// // Free result set
		// mysqli_free_result($result);
		// }

		// mysqli_close($con);

		// exit;
	}

	/**
	 * 匯入Excel資料表格
	 * @param  string  $fp2       打開的excel檔
	 * @param  int     $line      讀取幾行，預設全部讀取
	 * @param  int     $offset    從第幾行開始讀，預設從第一行讀取
	 * @return bool|array
	 */
	public function importCsv($fp2, $line, $offset)
	{
		//set_time_limit(0);//防止超時
		//ini_set("memory_limit", "512M");//防止記憶體溢位

		// $handle = fopen($fileName, 'r');
		if (!$fp2) {
			return  '檔案開啟失敗';
		}

		$i = 0;
		$j = 0;
		$arr = [];
		while ($data = fgetcsv($fp2)) {
			//小於偏移量則不讀取,但$i仍然需要自增
			if ($i < $offset && $offset) {
				$i++;
				continue;
			}
			//大於讀取行數則退出
			if ($i > $line && $line) {
				break;
			}


			foreach ($data as $key => $value) {
				$content = ($value); //轉化編碼

				$arr[$j][] = $content;
			}
			$i++;
			$j++;
		}
		// print_r($arr);
		// fclose($handle);
		return $arr;
	}

	/**
	 * 匯出Excel檔案 速度慢
	 * @param $fileName 匯出的檔名
	 * @param $headArr 資料頭
	 * @param $data 匯出資料
	 */
	public function getExcel($fileName, $headArr, $data)
	{
		//設定PHP最大單執行緒的獨立記憶體使用量
		ini_set('memory_limit', '1024M');
		//程式超時設定設為不限時
		ini_set('max_execution_time ', '0');

		//匯入PHPExcel類庫，因為PHPExcel沒有用名稱空間，所以使用vendor匯入
		vendor("PHPExcel.PHPExcel.IOFactory");
		vendor("Excel.PHPExcel");
		vendor("Excel.PHPExcel.Writer.Excel5");
		vendor("Excel.PHPExcel.IOFactory.php");

		//對資料進行檢驗
		if (empty($data) || !is_array($data)) {
			die("data must be a array");
		}
		//檢查檔名
		if (empty($fileName)) {
			exit;
		}
		$date = date("Y_m_d", time());
		$fileName .= "_{$date}.xls";
		//建立PHPExcel物件
		$objPHPExcel = new \PHPExcel();

		//設定表頭
		$key = ord("A");
		foreach ($headArr as $hkey => $v) {
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
			$key += 1;
			unset($headArr[$hkey]);
		}
		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach ($data as $key => $rows) { //行寫入
			$span = ord("A");
			foreach ($rows as $keyName => $value) { // 列寫入
				$j = chr($span);
				//設定匯出單元格格式為文字，避免身份證號的資料被Excel改寫
				$objActSheet->setCellValueExplicit($j . $column, $value);
				$span++;
				unset($rows[$keyName]);
			}
			$column++;
			unset($data[$key]);
		}
		$fileName = iconv("utf-8", "gb2312", $fileName);
		//重新命名錶
		// $objPHPExcel->getActiveSheet()->setTitle('test');
		//設定活動單指數到第一個表,所以Excel開啟這是第一個表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();
		ob_start();
		header('Content-Type: application/vnd.ms-excel'); //定義輸出的檔案型別為excel檔案
		header("Content-Disposition: attachment;filename=\"$fileName\""); //定義輸出的檔名
		header('Cache-Control: max-age=0'); //強制每次請求直接傳送給源伺服器，而不經過本地快取版本的校驗。
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output'); //檔案通過瀏覽器下載
		exit;
	}

	function img_gray($path)
	{
		$imginfo = getimagesize($path);
		$type = end($imginfo);

		switch ($type) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg($path);
				break;
			case 'image/png':
				$image = imagecreatefrompng($path);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($path);
				break;
			case 'image/bmp':
				$image = imagecreatefrombmp($path);
				break;
			default:
				print("Image type exist by $type");
				exit;
		}

		$size = getimagesize($path);
		imagefilter($image, IMG_FILTER_GRAYSCALE);	//灰度 https://www.php.net/manual/zh/function.imagefilter.php
		$white = imagecolorallocate($image, 255, 255, 255); //去黑邊
		echo "<img src='$path' /></br>";
		$black = imagecolorallocate($image, 0, 0, 0); //去黑邊
		echo "<img src='$path' /></br>";
		
		// 十六进制方式
		$white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		echo "<img src='$path' /></br>";
		$black = imagecolorallocate($image, 0x00, 0x00, 0x00);
		echo var_dump($black)."</br>";
		echo "<img src='$black' /></br>";
		imageline($image, 0, 0, $size[0], 0, $white);
		imageline($image, 0, 0, 0, $size[1], $white);
		imageline($image, $size[0] - 1, 0, $size[0] - 1, $size[1] - 1, $white);
		imageline($image, 0, $size[1] - 1, $size[0] - 1, $size[1] - 1, $white);
		$img = [];
		for ($y = 0; $y < $size[0]; $y++) {
			for ($x = 0; $x < $size[1]; $x++) {
				if ($y === 0 || $x === 0 || $y === $size[0] - 1 || $x === $size[1] - 1) {
					$img[$x][$y] = 1;
					continue;
				}
				$rgb = imagecolorat($image, $y, $x);
				$rgb = imagecolorsforindex($image, $rgb);

				if ($rgb['red'] > 71 && $rgb['green'] > 71 && $rgb['blue'] > 71) {
					$img[$x][$y] = 0;
				} else {
					$img[$x][$y] = 1;
				}
			}
		}
		for ($y = 0; $y < $size[1]; $y++)
			for ($x = 0; $x < $size[0]; $x++)
				if ($img[$y][$x] == 0)
					imagesetpixel($image, $x, $y, $black);
				else
					imagesetpixel($image, $x, $y, $white);
		imagejpeg($image, $path); //保存
		imagedestroy($image); //釋放內存
	}
}
