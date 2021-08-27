 <?php
// namespace App\Console\Commands;
date_default_timezone_set("America/New_York");
require_once "./vendor/autoload.php";
// require_once "./GuzzleDataOption.php";
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

// function dump(...$arr){
//   foreach ($arr as $key => $value) {
//     if(is_array($value)){
//       print_r($value);
//     }else{
//       echo $value.PHP_EOL;
//     }
//   }
// }

// function dd(...$arr){
//   dump(...$arr);
//   exit();
// }
function color($str, $color = "Light Green")
{
    $arr = [
        'Black'         => '0;30',
        'Dark Grey'     => '1;30',
        'Red'           => '0;31',
        'Light Red'     => '1;31',
        'Green'         => '0;32',
        'Light Green'   => '1;32',
        'Brown'         => '0;33',
        'Yellow'        => '1;33',
        'Blue'          => '0;34',
        'Light Blue'    => '1;34',
        'Magenta'       => '0;35',
        'Light Magenta' => '1;35',
        'Cyan'          => '0;36',
        'Light Cyan'    => '1;36',
        'Light Grey'    => '0;37',
        'White'         => '1;37',
        'Light Red'     => '1;31',
        'Light Green'   => '1;32',
        'Light Blue'    => '1;34',
        'Yellow'        => '1;33',
        'Light Magenta' => '1;35',
    ];
    $col = isset($arr[$color]) ? $arr[$color] : $arr['Light Green'];
    return "\e[" . $col . ";43m" . $str . "\e[0m";
}

class setting
{
    const SETTING_FILE = "setting_a14.json";
    private $totalPageCount;
    private $delay = 0;
    private $client;

    private $counter = 1;
    protected $process;
    protected $setting;
    protected $db_con;

    public static $PDO_arr = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_TIMEOUT            => 10,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::NULL_EMPTY_STRING,
    );

    public function __construct()
    {
        ini_set("memory_limit", "2G");
        ini_set("max_execution_time", 0);

        $this->setting = $this->setting(self::SETTING_FILE);
        $this->header  = [];
        $this->client  = new Client([
            'base_uri' => $this->setting['url'],
        ]);
        // 供curl用
        foreach ($this->setting['headers'] as $key => $value) {
            array_push($this->header, $key . ': ' . $value);
        }

        // $option = new crawlerDataOption($this->setting);

        $this->db_setting();
        $this->mkdir_log('member_log');
        $this->mkdir_log('agency_log');

    }

    public function setting($json_file)
    {
        try {
            $setting = $json_file;
            if (is_file($json_file)) {
                if ($data = json_decode(@file_get_contents($setting), true)) {
                    return $data;
                } else {
                    throw new Exception($setting . "錯誤");
                }
            } else {
                throw new Exception("沒有" . $setting . "(請先" . color('cp setting.example.json setting.json', 'Red') . ")");
            }
        } catch (Exception $e) {
            print_r('錯誤: ' . $e->getMessage());
        }
    }

    //複寫Json
    public function updateTojson($dataArr = "", $filePath)
    {
        if (!is_array($dataArr) || !is_file($filePath)) {
            echo "寫入Json檔案錯誤" . PHP_EOL;
        } else {
            $json_strings = json_encode($dataArr, JSON_UNESCAPED_SLASHES);
            file_put_contents($filePath, $json_strings); //寫入
        }
    }

    public function db_setting()
    {
        foreach ($this->setting['db_connection'] as $key => $value) {
            $db                 = "mysql:host={$this->setting['db_host']};dbname={$value};charset=utf8;";
            $this->db_con[$key] = new PDO($db, $this->setting['db_user'], $this->setting['db_pass'], self::$PDO_arr);
        }
    }

    public function db_data($con, $sql)
    {
        try {
            return $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "sql : ";
            print_r($sql);
            print_r($e->getMessage());
        }
    }
    public function db_insert($table, $data, $replace = false)
    {
        try {
            $fields = [];
            foreach ($data as $k => $v) {
                $fields[] = "$k = :$k";
            }
            if ($replace) {
                $sql = "replace INTO {$table} SET " . implode(",", $fields);
            } else {
                $sql = "INSERT INTO {$table} SET " . implode(",", $fields);
            }
            $sth = $this->db_con['agency']->prepare($sql);
            foreach ($data as $key => $val) {
                $sth->bindValue(":{$key}", $val);
            }
            $sth->execute();
            if (isset($this->data_count)) {
                $this->data_count++;
            }
        } catch (Exception $e) {
            // echo color("\n 資料表:$table", 'Light Red');
            // echo color('遇到錯誤紀錄下來', 'Light Red');
            // echo color($e->getMessage(), 'Light Red');
            $this->save_csv('db_err.log', $table, $e->getMessage());
        }
    }

    public function db_update($table, $where, $data, $acc = [])
    {
        try {
            $fields_imp = function ($data) {
                $fields = [];
                foreach ($data as $k => $v) {
                    $fields[] = "$k = :$k";
                }
                return implode(",", $fields);
            };
            $fields_acc = function ($acc) {
                if (count($acc) > 0) {
                    $fields = [];
                    foreach ($acc as $k => $v) {
                        $fields[] = "$k = $v";
                    }
                    return ',' . implode(",", $fields);
                }
            };

            $sql = "UPDATE {$table} SET " . $fields_imp($data) . $fields_acc($acc) . " WHERE " . $fields_imp($where);
            $sth = $this->db_con['agency']->prepare($sql);
            foreach (array_merge($where, $data) as $key => $val) {
                $sth->bindValue(":{$key}", $val);
            }
            $sth->execute();
        } catch (Exception $e) {
            echo color("\n 資料表:$table", 'Light Red');
            echo color('遇到錯誤紀錄下來', 'Light Red');
            echo color($e->getMessage(), 'Light Red');
            $this->save_csv('db_err.log', 'table:' . $table . ' data:' . json_encode($acc), $e->getMessage());
        }
    }

    public function mkdir_log($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    public function rm_log($log)
    {
        foreach ($log as $key => $value) {
            if (is_file($value)) {
                unlink($value);
            }
        }
    }

    public function save_csv($log, $before, $after = null)
    {
        if (is_null($after)) {
            $str = $before;
        } else {
            $str = $before . ',' . $after;
        }

        file_put_contents($log, $str . PHP_EOL, FILE_APPEND);
    }

    public function read_csv($log)
    {
        $file = file_get_contents($log);
        $tmp  = explode("\n", $file);
        $data = [];
        foreach ($tmp as $key => $value) {
            if (isset($value) && $value != '') {
                $tmp_list           = explode(',', $value);
                $data[$tmp_list[0]] = isset($tmp_list[1]) ? $tmp_list[1] : $tmp_list[0];
            }
        }
        return $data;
    }

    public function Guzz_curl($url, $data, $header, $method = 'GET', $sql_fun = null, $begin = false, $special = true)
    {
        $recursive = function ($url, $header, $method, $sql_fun, $special) use (&$data, &$recursive) {
            print_r($this->setting['headers']);
            exit;
            if (count($this->setting['headers']) > 0) {
                $header = array_column(array_map(function ($item) {
                    $tmp = explode(': ', $item);
                    return [
                        'key'   => $tmp[0],
                        'value' => $tmp[1],
                    ];
                }, $this->setting['headers']), 'value', 'key');
            }
            // print_r($header);
            // exit;
            $this->totalPageCount = @count($data);
            $total                = $this->totalPageCount;
            $client               = new Client([
                'verify'  => false,
                // 'headers' => [
                //     "Accept"                    => "text/plain, */*; q=0.01",
                //     "Accept-Encoding"           => "gzip, deflate",
                //     "Accept-Language"           => "zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7",
                //     "Connection"                => "keep-alive",
                //     "Cookie"                    => $header['Cookie'],
                //     "Host"                      => "line.hg17118.com",
                //     // "Referer"                   => "http://line.hg559m.com/member/balance/".@$data[0]."/",
                //     "Upgrade-Insecure-Requests" => "1",
                //     "User-Agent"                => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36",
                //     // "Origin"                    => "http://line.hg559m.com",
                //     "X-Requested-With"          => "XMLHttpRequest",
                // ], // 可能要這樣帶
                // 'allow_redirects' =>false

            ]);
            // var_dump($client);
            // exit;
            $requests = function ($total) use ($client, $url, $data, $header, $method, $special) {
                if (is_array($url) && count($url) > 0) {
                    foreach ($url as $value) {

                        yield function () use ($client, $url, $header, $value, $method, $data) {
                            // print_r($url);
                            // exit;
                            $arr = [
                                'connect_timeout' => 10,
                                'timeout'         => 8,
                                'headers'         => $header, //正常情況這樣帶
                                // 'Referer'         => "http://line.hg17118.com/member/balance/" .     $value['userid'] . "?", //正常情況這樣帶
                            ];
                            if (isset($value['userid'])) {
                                $arr['Referer'] = "http://line.hg17118.com/member/balance/" . $value['userid'] . "?";
                            }
                            if (isset($this->setting['proxy'])) {
                                $arr['proxy'] = $this->setting['proxy'];
                                // echo 'proxy' . $this->setting['proxy'];
                            }
                            // if($method == 'TG'){
                            // print_r($value);
                            // exit;
                            if ($method == 'GET') {
                                $url = $value['url'] . '?' . @http_build_query($data); // http_build_query 陣列轉GET參數

                            } else {
                                $arr['body'] = $data;
                            }
                            // print_r($method);
                            // print_r($url);
                            // print_r($arr);
                            // exit;
                            // usleep(50000);
                            return $client->requestAsync($method, $url, $arr);
                        };
                    }
                } else {
                    foreach ($data as $value) {
                        // print_r($value);
                        //
                        yield function () use ($client, $url, $header, $value, $method, $special) {
                            $arr = [
                                'connect_timeout' => 10,
                                'timeout'         => 8,
                                'headers'         => $header, //正常情況這樣帶
                                'Referer'         => "http://line.hg17118.com/member/balance/" . $value['userid'] . "?", //正常情況這樣帶
                            ];
                            if (isset($this->setting['proxy'])) {
                                $arr['proxy'] = $this->setting['proxy'];
                                // echo 'proxy' . $this->setting['proxy'];
                            }
                            // if($method == 'TG'){

                            if ($method == 'GET') {
                                if ($special) {
                                    $url = $url . '?stype=' . $value['stype'];
                                } else {
                                    $url .= '?' . http_build_query($value); // http_build_query 陣列轉GET參數
                                }
                            } else {
                                $arr['body'] = $value['data'];
                            }
                            // print_r($method);
                            // print_r($url);
                            // print_r($arr);
                            // exit;
                            return $client->requestAsync($method, $url, $arr);
                        };
                    }
                }
            };

            $pool = new Pool($client, $requests($this->totalPageCount), [
                'concurrency' => $this->setting['concurrency'],
                'fulfilled'   => function ($response, $index) use (&$data, $sql_fun, $url) {
                    // $this->data_count++;
                    // echo "\e[1;32;40m请求第 $index 个请求成功!\e[0m\n";
                    $tmp_data = $response->getBody()->getContents();

                    $INDEX = (is_array($url) && count($url) > 0) ? $url[$index] : $data[$index];
                    if (isset($sql_fun)) {
                        // print_r($tmp_data);
                        $sql_fun($tmp_data, $INDEX);
                    }
                    unset($data[$index]);
                },
                'rejected'    => function ($reason, $index) {
                    // $this->data_count--;
                    echo $reason . PHP_EOL;
                    echo "\e[1;31;47m请求第 $index 个请求失敗!\e[0m\n";
                },
            ]);

            // 开始发送请求
            $promise = $pool->promise();
            $promise->wait();
            if (count($data) > 0) {
                $data = array_values($data);
                $str  = str_pad("请求失敗的有" . count($data) . "個!!!", 100, "~", STR_PAD_BOTH);
                echo "\e[1;37;40m $str 重新抓取失敗請求\e[0m\n";
                if (isset($data[0]['stype'])) {
                    if ($data[0]['stype'] == 'RG') {
                        return false;
                    }
                }
                sleep(1);
                // print_r($data);


                return $recursive($url, $header, $method, $sql_fun, $special);
            }
        };
        $data = $recursive($url, $header, $method, $sql_fun, $special);
        return $data;
    }

    public function Guzz_curl_new(array $data)
    {
        // var_dump($data);
        $client = $this->client;
        if (count($this->setting['headers']) > 0) {
            $header = array_column(array_map(function ($item) {
                $tmp = explode(': ', $item);
                return [
                    'key'   => $tmp[0],
                    'value' => $tmp[1],
                ];
            }, $this->setting['headers']), 'value', 'key');
        }
        // 合併與取代預設Header
        array_merge($header, $data['headers']);
        $recursive = function () use (&$data, &$recursive) {
            $requests = function ($total) {
                for ($i = 0; $i < $total; $i++) {
                    yield new Request('GET', $uri);
                }
            };

            $pool = new Pool($client, $requests(100), [
                // $data['concurrency'] ?? $this->setting['concurrency']
                // This is equivalent to: isset($data['concurrency']) ? $data['concurrency'] : $this->setting['concurrency'];
                // (PHP7.0)
                'concurrency' => $data['concurrency'] ?? $this->setting['concurrency'],
                'fulfilled'   => function (Response $response, $index) {
                    // this is delivered each successful response
                },
                'rejected'    => function (RequestException $reason, $index) {
                    // this is delivered each failed request
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();
            // Force the pool of requests to complete.
            $promise->wait();
        };
    }

    public function curl_send($url, $data, $method = 'POST')
    {

        $ch       = curl_init();
        $curl_opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            // CURLOPT_COOKIESESSION  => true,
            CURLOPT_HEADER         => 1,
            CURLOPT_HTTPHEADER     => $this->setting['headers'],
            // CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_URL            => $url,
            CURLOPT_ENCODING       => 'UTF-8',
            // CURLOPT_NOBODY       => true,
            // CURLOPT_CUSTOMREQUEST =>'POST'
        ];

        $curl_opt[CURLOPT_COOKIEJAR] = './cookie.txt';

        if ($method == 'POST') {
            $curl_opt[CURLOPT_URL]  = $url;
            $curl_opt[CURLOPT_POST] = true;
            if ($data != '') {
                $curl_opt[CURLOPT_POSTFIELDS] = urldecode(http_build_query($data));
            }
        } else if ($method == 'GET') {
            if ($data != '') {$curl_opt[CURLOPT_URL] = $url . '?' . @urldecode(@http_build_query($data));} else {
                $curl_opt[CURLOPT_URL] = $url;
            }
            // print_r($curl_opt[CURLOPT_URL]);
            // exit;
        } else {
            echo "error method!! \$method='" . $method . "'" . __LINE__;
            exit;
        }
        if (isset($this->setting['proxy']) && $this->setting['proxy'] != '') {
            $curl_opt[CURLOPT_PROXY] = $this->setting['proxy'];
            // dump('proxy' . $this->setting['proxy']);
        }
        curl_setopt_array($ch, $curl_opt);
        // var_dump($curl_opt);

        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $error    = curl_error($ch);
        print_r($error);
        curl_close($ch);
        if (isset($this->setting['curl_debug']) && $this->setting['curl_debug']) {
            echo "\e[1;47;34m\$url\e[0m";
        }
        return $response;
    }

    public function curl_send_new($url, $data, $method = 'POST', $header = [])
    {
        // $this->debug_msg("\$url", $url);
        // print_r($this->header);
        // exit;
        $ch       = curl_init();
        $curl_opt = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            //CURLOPT_COOKIESESSION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_URL            => $url,
            // CURLOPT_POST           => true,
            // CURLOPT_POSTFIELDS     => $data,
            CURLOPT_ENCODING       => 'UTF-8',
            // CURLOPT_COOKIEJAR      => './cookie.txt',
        ];
        $curl_opt[CURLOPT_URL] = $url;
        // var_dump($curl_opt);
        if ($method == 'POST') {
            $curl_opt[CURLOPT_POST] = true;
            if (count($data) > 0) {
                $curl_opt[CURLOPT_POSTFIELDS] = urldecode(http_build_query($data));
            }

        } else if ($method == 'GET') {
            if (count($data) > 0) {
                $curl_opt[CURLOPT_URL] = $url . '?' . urldecode(http_build_query($data));
            }
        } else {
            echo "error method!! \$method='" . $method . "'" . __LINE__;
            exit;
        }

        if (isset($this->setting['proxy']) && $this->setting['proxy'] != '') {
            $curl_opt[CURLOPT_PROXY] = $this->setting['proxy'];
            // dump('proxy' . $this->setting['proxy']);
        }
        curl_setopt_array($ch, $curl_opt);

        $response = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $error    = curl_error($ch);

        // print_r($info);
        // if(preg_match('/302 Found/',$info)  )
        print_r($response);

        // exit;
        curl_close($ch);
        if (isset($this->setting['curl_debug']) && $this->setting['curl_debug']) {
            echo "\e[1;47;34m\$url\e[0m";
        }

        return $response;
    }

    public function memory_use_now()
    {
        $level = array('Bytes', 'KB', 'MB', 'GB');
        $n     = memory_get_usage();
        for ($i = 0, $max = count($level); $i < $max; $i++) {
            if ($n < 1024) {
                $n = round($n, 2);
                return "{$n} {$level[$i]}";
            }
            $n /= 1024;
        }
    }

    public function debug_msg($name, $value)
    {
        echo PHP_EOL . "\e[1;47;30mDEBUG\t=>\t$name" . PHP_EOL;
        print_r($value);
        echo "\e[0m" . PHP_EOL;
    }

    public function light_alert($msg1 = null, $msg2 = null)
    {
        if (is_null($msg1) || empty($msg1)) {
            return "";
        } else if (!empty($msg1) && is_null($msg2)) {
            return " \e[1;34m" . $msg1 . "\e[0m" . PHP_EOL . "";
        }
        return " \e[1;34m  " . $msg1 . "   =>  " . $msg2 . "";
    }

    public function colorMsg($msg, $type = 'MSG')
    {
        $colorMsg = "";



        $colorEnd = "  \e[0m" . PHP_EOL;
        switch ($type) {
            case 'ERROR': // 會導致或讓程式結束的訊息(紅底白字)
                $colorSet = " \e[1;37;41m  " . $msg . "  IN " . __METHOD__ . " LINE: " . __LINE__;
                // echo " \e[1;37;41m  " . $msg . " \e[0m" . "  IN " . __METHOD__ . " LINE: " . __LINE__;
                break;
            case 'WARMING': // 例外跳出迴圈或進入例外處理(黃底紅字)
                $colorSet = " \e[0;31;43m";
                $colorSet = " \e[1;30;47m  " . $msg . "  IN " . __METHOD__ . " LINE: " . __LINE__;
                break;
            case 'INPUT': // 使用者輸入選項(黃字)
                $colorSet = " \e[0;36m";
                $colorSet = " \e[0;36m" . $msg;
                break;
            case 'DEBUG':
                $colorSet = " \e[1;31;43m";
                $colorSet = " \e[0;34;47m" . $msg;
                break;
            case 'TIME':
                $colorSet = " \e[36m" . $msg;
            case 'MSG': // 流程文字
            default:
                $colorSet = " \e[1;34m";
                $colorSet = " \e[1;34m" . $msg;
                break;
        }
        $colorSet .= $colorEnd;
        return $colorSet;
        // return $colorMsg;s
    }

    // 轉換為Guzzle用Header陣列
    public function headersToGuzzle(){
        $header = [];
        if(count($this->setting['headers']) > 0){
            foreach($this->setting['headers'] as $item){
                $tmp = explode(':', $item);
                if(isset($tmp[1])){
                    $header[$tmp[0]] = trim($tmp[1]);
                }else{
                    var_dump($tmp);
                    echo $this->colorMsg('Headers轉換錯誤', 'ERROR');
                }
            }
            return $header;
        }
        return false;
    }

    public function headerToCurl(array $headers)
    {

    }

    public function arrayToString($text, $stringMsg= ""){
        return function() use ($text, $stringMsg) {
            if(is_array($text)){
                foreach($text as $key => $value){
                    $stringMsg .= "$key => ";
                    $stringMsg .= $this->arrayToString($value,  $stringMsg);
                }
            }else if(is_string($text)){
                return $text;
            }else {
                return "[Can't Not Format]";
            }
        };
    }

public function save_one_img($capture_url,$img_url)
    {
        //图片路径地址
//        if ( strpos($img_url, 'http://')!==false )
//        {
//            // $img_url = $img_url;
//        }else
//        {
//            $domain_url = substr($capture_url, 0,strpos($capture_url, '/',8)+1);
//            $img_url=$domain_url.$img_url;
//        }
        $pathinfo = pathinfo($img_url);    //获取图片路径信息
        $pic_name=$pathinfo['basename'];   //获取图片的名字
        if (file_exists($this->save_path.$pic_name))  //如果图片存在,证明已经被抓取过,退出函数
        {
            echo $img_url . '<span style="color:red;margin-left:80px">该图片已经抓取过!</span><br/>';
            return;
        }
        //将图片内容读入一个字符串
//        dd($img_url);
//        info($img_url);
        $img_data = @file_get_contents($img_url);   //屏蔽掉因为图片地址无法读取导致的warning错误

        if ( strlen($img_data) > $this->img_size )   //下载size比限制大的图片
        {
            $img_size = file_put_contents($this->save_path . $pic_name, $img_data);
            if ($img_size)
            {
                echo $img_url . '<span style="color:green;margin-left:80px">图片保存成功!</span><br/>';
            } else
            {
                echo $img_url . '<span style="color:red;margin-left:80px">图片保存失败!</span><br/>';
            }
        } else
        {
            echo $img_url . '<span style="color:red;margin-left:80px">图片读取失败!</span><br/>';
        }
    }
}
