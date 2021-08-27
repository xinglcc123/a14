<?php
	include('../class.php');

    class Phoenix extends setting
    {
    	public function __construct()
    	{
    		parent::__construct();
    		$this->setting = $this->setting('phoenix.json');
    		$this->db_setting();
    		$this->mkdir_log('member_log');
    	}

		public function index()
		{
			$process_arr = ['會員列表','會員詳情','銀行卡'];
			echo "請輸入項目\n";
			echo "[0=>'會員列表',1=>'會員詳情',2=>'銀行卡']\n";
			$handle = fopen ("php://stdin","r");
			$process = trim(fgets($handle));
			fclose($handle);
			switch ($process) {
				case '0':
					$this->member_list();
					break;
				case '1':
					$this->member_detail();
					break;
				case '2':
					$this->member_bank();
					break;
				default:
				    echo "請輸入正確數字".PHP_EOL;
				    $this->index();
				    break;
			}
		}

		public function member_list($start = '',$end = '')
		{
			if($start != '' && $end != ''){
				$replace = true;
			}else{
				$replace = false;
			}
			$member_data = [];
			$url = $this->setting['url'].'admin/account/list.do';
			$data = [
				'sortOrder' => 'asc',
				'pageSize' => '20',
				'pageNumber' => '1',
				'undefined' => '',
				'startDate' => '',
				'username' => '',
				'levelIds' => '',
				'accountType' => '',
				'keywordType' => '1',
				'keyword' => '',
				'proxyPromoCode' => '',
				'endDate' => '',
				'exactUsername' => '',
				'proxyName' => '',
				'agentUser' => '',
				'depositStatus' => '',
				'drawStatus' => '',
				'money' => '',
				'notLoginDay' => '',
				'level' => '',
				'lastLoginIp' => '',
			];
			$total = json_decode($this->curl_post($url,$data,$this->setting['header']),true)['total'];
			$total_page = ceil($total/1000);
			$data['pageSize'] = 1000;
			for ($page = 1; $page <= $total_page; $page++) { 
				echo "正在撈取第".$page."頁資料\n";
				$data['pageNumber'] = $page;
				$tmp_data = json_decode($this->curl_post($url,$data,$this->setting['header']),true)['rows'];
				foreach ($tmp_data as $key => $value) {
					$remark = (isset($value['remark'])) ? $value['remark'] : '';
					$status = ($value['status'] == 2) ? 1 : 0;
					$useracc = (isset($value['username'])) ? strtolower($value['username']) : '';
					$username = (isset($value['realName'])) ? $value['realName'] : '';
					$registed_at = (isset($value['createDatetime'])) ? $value['createDatetime'] : '';
					$lastlogin_at = (isset($value['lastLoginDatetime'])) ? $value['lastLoginDatetime'] : '';
					if(!preg_match("/^[^(||#|@|~|$|!|%|^|*|&|(|)|-|_|+|=|0-9)]+$/",$username) || $username == ''){
						$this->save_csv($this->setting['log']['name_error'],$useracc);
						continue;
					}
					if(preg_match("/^[a-z0-9]{6,".$this->setting['member_length']."}$/",$useracc)){
						$member_data = [
							'userid' => $value['id'],
							'useracc' => $useracc,
							'username' => $username,
							'status' => $status,
							'registed_at' => $registed_at,
							'lastlogin_at' => $lastlogin_at,
							'remark' => $remark,
						];
						echo "{$value['username']}新增\n";
						$this->db_insert("tmp_".$this->setting['prefix']."_account",$member_data,$replace);
					}else{
						$this->save_csv($this->setting['log']['length_error'],$value['username']);
					}
				}
			}
		}

		public function member_detail()
		{
			$data = [];
			$member_id = $this->db_data("SELECT `userid`,`useracc` FROM `tmp_".$this->setting['prefix']."_account` WHERE `check_detail` = 0");
			foreach ($member_id as $key => $value) {
				$data [] = [
					'id' => $value['userid'],
					'_' => '1585712711145',
				];
			}
			$url = $this->setting['url'].'admin/account/detail.do';
			$sql_fun = function($tmp_data,$member){
				$dom =new \domDocument;
				@$dom->loadHTML(mb_convert_encoding($tmp_data, 'HTML-ENTITIES', 'UTF-8'));
				$trDom = $dom->getElementsByTagName("table")->item(0)->getElementsByTagName("tr");
				$detail_data = [];
				$account = $trDom->item(1)->getElementsByTagName("td")->item(0)->nodeValue;
				$detail_data = [
					'mobile' => $trDom->item(9)->getElementsByTagName("td")->item(0)->nodeValue,
					'wechat' => $trDom->item(9)->getElementsByTagName("td")->item(1)->nodeValue,
					'qqskype' => $trDom->item(10)->getElementsByTagName("td")->item(0)->nodeValue,
					'email' => $trDom->item(10)->getElementsByTagName("td")->item(1)->nodeValue,
					'withdrawal_count' => $trDom->item(14)->getElementsByTagName("td")->item(0)->nodeValue,
					'withdrawal' => $trDom->item(14)->getElementsByTagName("td")->item(1)->nodeValue,
					'deposit_count' => $trDom->item(18)->getElementsByTagName("td")->item(0)->nodeValue,
					'deposit' => $trDom->item(18)->getElementsByTagName("td")->item(1)->nodeValue,
					'check_detail' => '1',
				];
				$balance = $trDom->item(4)->getElementsByTagName("td")->item(1)->nodeValue;
				$span_arr = $trDom->item(7)->getElementsByTagName("td")->item(0)->getElementsByTagName("span");
				foreach ($span_arr as $span) {
					$balance += $span->nodeValue;
				}
				$detail_data['balance'] = $balance;
				echo "{$account}更新詳情";
				$this->db_update("tmp_".$this->setting['prefix']."_account",['userid' => $member['id']],$detail_data);
			};
			$this->Guzz_curl($url,$data,$this->setting['header'],'GET',$sql_fun);
		}

		public function member_bank()
		{
			$url = $this->setting['url'].'admin/accountBank/list.do';
			$data = [
				'sortOrder' => 'asc',
				'pageSize' => '20',
				'pageNumber' => '1',
				'username' => '',
				'realname' => '',
				'cardNo' => '',
				'bankName' => '',
				'bankAddress' => '',
				'levelIds' => '',
			];
			$total = json_decode($this->curl_post($url,$data,$this->setting['header']),true)['total'];
			$total_page = ceil($total/1000);
			$data['pageSize'] = 1000;
			for ($page = 1; $page <= $total_page; $page++) { 
				echo "正在撈取第".$page."頁資料\n";
				$data['pageNumber'] = $page;
				$tmp_data = json_decode($this->curl_post($url,$data,$this->setting['header']),true)['rows'];
				foreach ($tmp_data as $key => $value) {
					// $remark = (isset($value['remark'])) ? $value['remark'] : '';
					// $status = ($value['status'] == 2) ? 1 : 0;
					// $useracc = (isset($value['username'])) ? strtolower($value['username']) : '';
					// $username = (isset($value['realName'])) ? $value['realName'] : '';
					// $registed_at = (isset($value['createDatetime'])) ? $value['createDatetime'] : '';
					// $lastlogin_at = (isset($value['lastLoginDatetime'])) ? $value['lastLoginDatetime'] : '';
					// if(!preg_match("/^[^(||#|@|~|$|!|%|^|*|&|(|)|-|_|+|=|0-9)]+$/",$username) || $username == ''){
					// 	$this->save_csv($this->setting['log']['name_error'],$useracc);
					// 	continue;
					// }
					// if(preg_match("/^[a-z0-9]{6,".$this->setting['member_length']."}$/",$useracc)){
					// 	$member_data = [
					// 		'userid' => $value['id'],
					// 		'useracc' => $useracc,
					// 		'username' => $username,
					// 		'status' => $status,
					// 		'registed_at' => $registed_at,
					// 		'lastlogin_at' => $lastlogin_at,
					// 		'remark' => $remark,
					// 	];
					// 	echo "{$value['username']}新增\n";
					// 	$this->db_insert("tmp_".$this->setting['prefix']."_account",$member_data,$replace);
					// }else{
					// 	$this->save_csv($this->setting['log']['length_error'],$value['username']);
					// }
				}
			}
		}
    }

    $startTime = date('H:i:s');
    $run = new Phoenix();
    $run->index();
    $endTime = date('H:i:s');
    echo "開始時間:{$startTime}\n";
    echo "結束時間:{$endTime}\n";
?>