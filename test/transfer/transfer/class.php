<?php
	require_once(__DIR__.'/../vendor/autoload.php');
	use GuzzleHttp\Client;
	use GuzzleHttp\Pool;

	function color($str,$color){
		$arr = [
			'Black'         =>'0;30',
			'Dark Grey'     =>'1;30',
			'Red'           =>'0;31',
			'Light Red'     =>'1;31',
			'Green'         =>'0;32',
			'Light Green'   =>'1;32',
			'Brown'         =>'0;33',
			'Yellow'        =>'1;33',
			'Blue'          =>'0;34',
			'Light Blue'    =>'1;34',
			'Magenta'       =>'0;35',
			'Light Magenta' =>'1;35',
			'Cyan'          =>'0;36',
			'Light Cyan'    =>'1;36',
			'Light Grey'    =>'0;37',
			'White'         =>'1;37',
		];
		$col = isset($arr[$color]) ? $arr[$color]:$arr['White'];
		return "\e[$col;m$str\e[0m";
	}

	class setting
	{
		public function __construct()
		{
			date_default_timezone_set('Asia/Taipei');
			ini_set("memory_limit","2G");
			ini_set("max_execution_time",0);
		}

		public static $PDO_arr=array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			PDO::ATTR_TIMEOUT => 10,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::NULL_EMPTY_STRING,
		);

		public function setting($setting){
			try {
				if(is_file($setting)){
					if($data = json_decode(@file_get_contents($setting),true)){
						return $data;
					}else{
						throw new Exception($setting."錯誤");
					}
				}else{
					throw new Exception("沒有".$setting."(請先". color('產生'.$setting,'Red') .")");
				}
			} catch (Exception $e) {
				echo '錯誤: '.$e->getMessage();
			}
		}

		public function mkdir_log($dir){
			if(!is_dir($dir)){
				mkdir($dir);
			}
		}

		public function save_csv($log,$before,$after=null){
			if(is_null($after)) $str = $before;
			else $str = $before.','.$after;
			file_put_contents($log,$str.PHP_EOL,FILE_APPEND);
		}

		public function db_setting(){
			$db="mysql:host={$this->setting['db_host']};dbname={$this->setting['prefix']};charset=utf8;";
			$this->db_agency = new PDO($db,$this->setting['db_user'],$this->setting['db_pass'],self::$PDO_arr);
		}

		public function db_data($sql){
			try {
				return $this->db_agency->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			} catch (Exception $e) {
				echo 'sql: '.$sql.PHP_EOL;
				echo '錯誤: '.$e->getMessage();
			}
		}

		public function db_insert($table,$data,$replace=false){
			try {
				$fields = [];
				foreach($data as $k=>$v)
				{
					$fields[] = "$k = :$k";
				}
				if($replace){
					$sql = "replace INTO {$table} SET ".implode(",",$fields);
				}else{
					$sql = "INSERT INTO {$table} SET ".implode(",",$fields);
				}
				$sth = $this->db_agency->prepare($sql);
				foreach ($data as $key => $val) {
					$sth->bindValue(":{$key}", $val);
				}
				$sth->execute();
			} catch (Exception $e) {
				echo color("\n 資料表:$table",'Light Red');
				echo color('遇到錯誤紀錄下來','Light Red');
				echo color($e->getMessage(),'Light Red');
				$this->save_csv('db_err.log',$table,$e->getMessage());
			}
		}

		public function db_update($table,$where,$data,$acc = []){
			try {
				$fields_imp = function($data){
					$fields = [];
					foreach($data as $k=>$v){
						$fields[] = "$k = :$k";
					}
					return implode(",",$fields);
				};
				$fields_acc = function($acc){
					if(count($acc) > 0){
						$fields = [];
						foreach($acc as $k=>$v){
							$fields[] = "$k = $v";
						}
						return ','.implode(",",$fields);
					}
				};   

				$sql = "UPDATE {$table} SET ".$fields_imp($data).$fields_acc($acc)." WHERE ".$fields_imp($where);
				$sth = $this->db_agency->prepare($sql);
				foreach (array_merge($where,$data) as $key => $val) {
					$sth->bindValue(":{$key}", $val);
				}
				$sth->execute();
			} catch (Exception $e) {
				echo color("\n 資料表:$table",'Light Red');
				echo color('遇到錯誤紀錄下來','Light Red');
				echo color($e->getMessage(),'Light Red');
				$this->save_csv('db_err.log','table:'.$table.' data:'.json_encode($acc),$e->getMessage());
			}
		}

		public function curl_get($url,$header = array()){
			$ch = curl_init();
			$curl_opt   = [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_COOKIESESSION => true,
				//CURLOPT_HEADER => true,
				CURLOPT_HTTPHEADER =>  $header,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_TIMEOUT => 120,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_FORBID_REUSE => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_ENCODING => "UTF-8",
				CURLOPT_URL => $url,
			];
			if(isset($this->setting['proxy'])){
				$curl_opt[CURLOPT_PROXY] = $this->setting['proxy'];
				dump('proxy'.$this->setting['proxy']);
			}        
			curl_setopt_array($ch, $curl_opt);
			$contents   = curl_exec($ch);
			$error      = curl_error($ch);
			curl_close($ch);
			return $contents;
		}

		public function curl_post($url, $data,$header = array()) {
			$ch         = curl_init();
			$curl_opt   = [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				//CURLOPT_COOKIESESSION => true,
				//CURLOPT_HEADER => true,
				CURLOPT_HTTPHEADER =>  $header,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_TIMEOUT => 120,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_FORBID_REUSE => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_URL => $url,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_ENCODING => 'UTF-8',
			];
			if(isset($this->setting['proxy'])){
				$curl_opt[CURLOPT_PROXY] = $this->setting['proxy'];
				dump('proxy'.$this->setting['proxy']);
			}
			curl_setopt_array($ch, $curl_opt);
			$response       = curl_exec($ch);
			$error          = curl_error($ch);
			curl_close($ch);
			return $response;
		}

		public function Guzz_curl( $url, $data, $header=[], $method='GET',$sql_fun=null,$begin=false){
			$header = array_column(array_map(function($item){
				$tmp = explode(':', $item);
				return [
					'key' => $tmp[0],
					'value' => $tmp[1],
				];
			},$header),'value','key');
			$recursive = function($url,$header,$method,$sql_fun) use (&$data,&$recursive)
			{
				$this->totalPageCount = count($data);
				$total = $this->totalPageCount;
				$client = new Client();
				$requests = function ($total) use ($client,$url,$data,$header,$method) {
					foreach ($data as $value) {
						yield function() use ($client, $url,$header,$value,$method) {
							$arr = [
								'connect_timeout'   => 60,
								'timeout'       => 60,
								'headers'       => $header,
							];
							if(isset($this->setting['proxy'])){
								$arr['proxy'] = $this->setting['proxy'];
								echo 'proxy'.$this->setting['proxy'];
							}
							if($method == 'GET'){
								$url .= '?'.http_build_query($value);
							}else{
								$arr['body'] = $value['data'];
							}
							return $client->requestAsync($method,$url,$arr);
						};
					}
				};

				$pool = new Pool($client, $requests($this->totalPageCount), [
					'concurrency' => $this->setting['concurrency'],
					'fulfilled'   => function ($response, $index) use (&$data,$sql_fun){
						echo "\e[1;32;40m请求第 $index 个请求成功!\e[0m\n";
						$tmp_data = $response->getBody()->getContents();
						if(isset($sql_fun)){
							$sql_fun($tmp_data,$data[$index]);
						}
						unset($data[$index]);
					},
					'rejected' => function ($reason, $index){
						echo $reason.PHP_EOL;
						echo "\e[1;31;47m请求第 $index 个请求失敗!\e[0m\n";
					},
				]);

				// 开始发送请求
				$promise = $pool->promise();
				$promise->wait();
				if(count($data) > 0 ){
					$data = array_values($data);
					$str = str_pad("请求失敗的有".count($data)."個!!!",100,"~",STR_PAD_BOTH);
					echo "\e[1;37;40m $str 重新抓取失敗請求\e[0m\n";
					sleep(3);
					return $recursive($url,$header,$method,$sql_fun);
				}
			};
			$data = $recursive($url,$header,$method,$sql_fun);
			return $data;
		}
	}
?>