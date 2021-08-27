<?php
// namespace App\Console\Commands;
// date_default_timezone_set("America/New_York");
// require_once "./vendor/autoload.php";
// require_once "./GuzzleDataOption.php";
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
// use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Pool;

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
    const SETTING_FILE = "setting.json";
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
            echo $this->colorMsg("資料表:$table", "ERROR");
            echo $this->colorMsg("遇到錯誤紀錄下來", "ERROR");
            echo $this->colorMsg($e->getMessage(), "WARMING");
            $this->save_csv('db_Insert_ERROR.csv', $table, $e->getMessage());
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

    public function Guzz_curl_new($url, $data, $method = 'GET', $sql_fun, $requestHeader = [])
    {

        $count = 0;
        $header = array_column(array_map(function ($item) {
                $tmp = explode(': ', $item);
                return [
                    'key'   => $tmp[0],
                    'value' => $tmp[1],
                ];
            }, $this->setting['headers']), 'value', 'key');
        // 請求數量
        $pageSet    = false;
        $page_array = [];
        if (count($page_array) < 1) {
            if (is_array($url) && count($url) > 0) {
                $dataArr = $url;
            } else {
                $dataArr = $data;
            }
        } else {
            $dataArr = $page_array;
            $pageSet = true;
        }
        // var_dump($header);
        // exit;
        $recursive = function ($url, $header, $method, $sql_fun) use (&$data, &$recursive, &$dataArr, $pageSet) {
            $total = count($dataArr);
            // var_dump($url);
            // var_dump($data);
            // var_dump($sql_fun);
            // echo $this->colorMsg('請求數量 => ' . $total, 'MSG');
            // $cJar   = new FileCookieJar('3369_cookies.txt', true);
            $client = new \GuzzleHttp\Client([
                // 'cookies' => $cJar,
            ]);

            $requests = function ($total) use ($url, $header, $method, $client, &$data, &$dataArr, $pageSet) {
                // var_dump($client);

                foreach ($dataArr as $value) {
                    // 請求頁數合併$data
                    if ($pageSet) {
                        $value = array_merge($data, $value);
                    }
                    yield function () use ($client, $url, $header, $method, $value, $data) {
                        $arr = [
                            'connect_timeout' => 70,
                            'timeout'         => 10,
                            'headers'         => $header, //正常情況這樣帶
                        ];
                        var_dump($arr);
                        exit;
                        if (isset($value['Referer'])) {
                            $arr['Referer'] = $value['Referer'];
                        }
                        if (isset($this->setting['proxy']) && trim($this->setting['proxy']) != "") {
                            $arr['proxy'] = $this->setting['proxy'];
                        }
                        if ($method == 'GET') {
                            if (is_array($url) && count($url) > 0) {
                                $url = $value['url'] . '?' . http_build_query($data);
                            } else {
                                $url = $url . '?' . @http_build_query($value); // http_build_query 陣列轉GET參數
                            }
                        } else {
                            $arr['form_params'] = $value;
                        }
                        // var_dump($value['url']);
                        // var_dump($url);
                        // var_dump($arr);
                        // exit;
                        return $client->requestAsync($method, $url, $arr);
                    };
                }
            };

            $pool = new Pool($client, $requests($total), [
                'concurrency' => $data['concurrency'] ?? $this->setting['concurrency'],
                'fulfilled'   => function ($response, $index) use ($sql_fun, &$dataArr) {
                    var_dump($response);
                    $tmp_data = $response->getBody()->getContents();
                    if ($sql_fun) {
                        $sql_fun($tmp_data, $dataArr[$index]);
                        unset($dataArr[$index]);
                    }
                },
                'rejected'    => function ($reason, $index) use (&$dataArr) {
                    echo $reason;


                    if (preg_match('/timed out after.*milliseconds with 0 bytes received/', $reason)) {
                        echo $this->colorMsg('原因:Time Out', 'ERROR');
                        if(preg_match('/http:\/\/line.21393.com\/[^ ]*/', $reason, $reasonUrl)){
                            echo $this->colorMsg('失败URL: '.$reasonUrl[0], 'ERROR');
                        }
                        $this->save_csv('GetPlantFormBalance_'.$dataArr[$index]['stype'].'_Error.csv', $dataArr[$index]['userid'], $dataArr[$index]['username'].','.$dataArr[$index]['stype']);
                    } else {
                        echo $reason;
                    }
                    echo "\e[1;31;47m请求第 $index 个请求失敗!\e[0m\n";
                    unset($dataArr[$index]);
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();
            // Force the pool of requests to complete.
            $promise->wait();
            // print_r($dataArr);
            if (count($dataArr) > 0) {
                $dataArr = array_values($dataArr);
                $str     = str_pad("请求失敗的有" . count($dataArr) . "個!!!", 100, "~", STR_PAD_BOTH);

                if (isset($dataArr[0]['stype'])) {
                    if ($dataArr[0]['stype'] == 'SS') {
                        return false;
                    }
                }
                echo PHP_EOL . "\e[1;37;40m $str" . PHP_EOL . " 重新抓取失敗請求\e[0m" . PHP_EOL;
                return $recursive($url, $header, $method, $sql_fun);
            }

        };
        $data_recursive = $recursive($url, $header, $method, $sql_fun);
        return $data_recursive;
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
        // print_r($url);/
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
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36',
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_URL            => $url,
            // CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
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
        curl_close($ch);
        print_r($response);
    exit;
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
                $colorSet = " \e[1;37;41m  " . $msg;
                // echo " \e[1;37;41m  " . $msg . " \e[0m" . "  IN " . __METHOD__ . " LINE: " . __LINE__;
                break;
            case 'WARMING': // 例外跳出迴圈或進入例外處理(黃底白字)
                $colorSet = " \e[0;31;43m";
                $colorSet = " \e[1;37;44m  " . $msg;
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
    }
}
