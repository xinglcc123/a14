 <?php
// namespace App\Console\Commands;
require_once "./vendor/autoload.php";
// require_once "./VerificationCodeHelper.php";

include './class_a14.php';
// $setting = new setting();
class Crawler extends setting
{
    protected $setting;
    protected $GuzzleHeader=[];
    protected $user = "";
    protected $class;
    protected $PlatformTypeArr = ['cp','ty','bb','ag','ibc','pt','mwg','lebo','ds','ab','pp','cmd','vg','vgs','cq9','bc','bg','png','jdb','fg','ky','nw','lg','dt','wm','sc','bsp','ebet','sg','pg','mgp','th','ogp','tn'];

    public function __construct(string $user = "")
    {
        // print_r ($this->colorMsg(["測試","123"],"WARMING"));

        // echo "\e[0;47;30m\e[?25h TESTTESTTEST \e[0m";
        // exit;
        parent::__construct();
        echo $this->colorMsg("請輸入後臺帳號?(輸入0則使用預設)");
        $account = trim(fgets(STDIN));

        $textFindKeyInArray = function(array $array, $text){
            $findKey = false;
            foreach ($array as $key => $value) {
                if (preg_match("/$text/", $value)) {
                    $findKey = $key;
                    return $findKey;
                }
            }
            return false;
        };

        if($account != "" && !is_null($account) && $account !==false){
            if($account === '0'){
                echo $this->colorMsg("使用預設帳號 {$this->setting['account']}", "MSG");
            }else{
                $this->setting['account'] = $account;
                $this->user = $account;
                echo $this->colorMsg("使用帳號 $account 更新Json檔案，註銷Cookie", "MSG");
                $CookieKey = $textFindKeyInArray($this->setting['headers'], 'Cookie');
                // print_r($CookieKey);
                // exit;
                if($CookieKey){
                    // 更換帳號註銷Cookie，避免帳號被黑單
                    unset($this->setting['headers'][$CookieKey]);
                }
                $this->updateTojson($this->setting, self::SETTING_FILE);
                $this->checkLogin();
            }
        }
        exit;


        // if ($user == "") {
        //     $this->light_alert("未輸入帳號，使用預設帳號", $this->setting['account']);
        //     sleep(1);
        //     $this->user = $this->setting['account'];
        // } else {
        //     $this->setting['account'] = $user;
        //     $this->user               = $user;
        //     $this->light_alert("更新帳號為:{$this->setting['account']}", "更新JSON檔案");
        //     $this->updateTojson($this->setting, self::SETTING_FILE);
        // }
        // print_r($this->headersToGuzzle());
        // print_r($this->headersToGuzzle());
        // print_r($this->headersToGuzzle());

        // var_dump($this->arrayToString(["a"=>"123", "b"=>["c"=>"456", "d"=>"789"]]));
            // exit;


    }
    public function index()
    {

        // $poscessTimeArray = []; // ["begin"=>["Unix" => "14564545.17", "msg" => "程式總執行時間:"]]
        // $ProcessTime      = function ($timeTag = null, $msg = null) use (&$poscessTimeArray) {
        //     if (!is_null($timeTag)) {
        //         if (isset($poscessTimeArray[$timeTag])) {
        //             if (!is_null($msg)) {
        //                 $echoMsg = $msg;
        //             } else {
        //                 $echoMsg = $poscessTimeArray[$timeTag]['msg'];
        //             }
        //             echo $this->colorMsg("執行時間 ($echoMsg) =>" . (microtime(true) - $poscessTimeArray[$timeTag]['Unix']) . "秒", "TIME");
        //             unset($poscessTimeArray[$timeTag]);
        //         } else {
        //             $poscessTimeArray[$timeTag]['Unix'] = microtime(true);
        //             if (is_null($msg)) {
        //                 $poscessTimeArray[$timeTag]['msg'] = "";
        //             } else {
        //                 $poscessTimeArray[$timeTag]['msg'] = $msg;
        //             }
        //         }
        //     } else {
        //         $this->colorMsg("\$timeTag值未設定", "WARMING");
        //     }
        // };
        // $ProcessTime();

        echo $this->colorMsg("[ 1=>'會員主頁 11=>更新', 2=>'爬取打碼量' 3=>'爬取會員厅室餘額' 4=>'针对各厅式捞取余额' 9=>'TG後台登入']" . PHP_EOL . "請輸入:", "INPUT");
        $handle  = fopen("php://stdin", "r");
        $process = trim(fgets($handle));
        fclose($handle);
        // if ($this->checkLogin() === false) {
        //     echo "\e[1;31;43m登入或請求異常!\e[0m" . PHP_EOL;
        //     exit;
        // }

        switch ($process) {
            case '1':
            case '11':
                if($process==11){
                    $this->new_member_list(true);
                    $this->up_member_balence2();
                    $this->PlantformGet();
                }else{
                    $this->new_member_list();
                }

                // $this->PlantformGet();
                break;
            case '2':
                // $this->member_pop_list();
                $this->TG_login();
                break;
            case '3':
                // $this->member_detail();
                $this->up_member_balence2();
                break;
            case '4':
                // $this->member_detail();
                $this->PlantformGet();
                break;
            case '5':
                $this->new_agent_list();
                break;
            case '9':
                $this->TG_login();
                break;
            default:
                echo "請輸入正確數字" . PHP_EOL;
                $this->index();
                break;
        }
    }
    public function checkLogin($Type = 'TG')
    {
        // $tmp_data = $response;
        $data     = [];
        $url      = $this->setting['url'];
        $response = $this->curl_send_new($url, $data, 'GET', []);
        // print_r($response);
        // exit;

        // if 302跳轉到主頁/index/admin 則驗證成功
        // @todo 分平台分析回傳文字
        switch ($Type) {
            case 'TG':
                if (preg_match('/302 Found/', $response) && preg_match('/Location: \/index\/admin/', $response)) {
                    echo $this->colorMsg("原Cookie登入成功，準備執行。。。", "MSG");
                    return true;
                } else if (preg_match('/登 入/', $response)) {
                    echo $this->colorMsg("Cookie已失效，重新取得Cookie", "WARMING");
                    if ($this->TG_login()) {
                        return true;
                    } else {
                        echo $this->colorMsg("取得Cookie異常", "ERROR");
                    }
                } else if ($response === "" || is_null($response) || $response === false) {
                    print_r($response);
                    echo $this->colorMsg("請求異常!\e[0m");
                } else {
                    // 其他原因，先嘗試登入
                    if ($this->TG_login()) {
                        return true;
                    }
                }
                break;
            default:
                echo $this->colorMsg("$type 設定異常", "ERROR");
                break;
        }

        return false;
    }

    // 會員資料抓取
    public function new_member_list(bool $replace=false)
    {
        $this->count_test = 0;
        $date_convert     = function ($date_data) {
            // $now = date("Y-m-d H:i:s");
            $stNow = strtotime("now");
            // var_dump($date_data);

            preg_match_all('/((\d*)天)?((\d*)时)?((\d*)分)?((\d*)秒)?/', $date_data, $output_array);
            // print_r($output_array);gki
            // exit;
            if (!is_null($output_array) && isset($output_array[7]) && isset($output_array[7][0])) {
                $date_day  = ($output_array[2][0] == "") ? "0" : $output_array[2][0];
                $date_hour = ($output_array[4][0] == "") ? "0" : $output_array[4][0];
                $date_min  = ($output_array[6][0] == "") ? "0" : $output_array[6][0];
                $date_sec  = ($output_array[7][0] == "") ? "0" : $output_array[7][0];

                $datetosec = (($date_day != "") ?: 0) * 86400 + (($date_hour != "") ?: 0) * 3600 + (($date_min != "") ?: 0) * 60 + $date_sec;
                $cv_date   = date("Y-m-d H:i:s", ($stNow - $datetosec));
                return $cv_date;
            } else {
                echo "\e[1;31;43時間轉換錯誤\e[0m" . PHP_EOL;
                print_r($date_data);
                exit;
            }
        };
        // $this->checkLogin();
        // exit;
        $this->insertCount = 0;
        $this->updateCount = 0;
        $data              = [
            'searchKey' => 'username',
            'searchVal' => '',
            'timeType'  => 'ltime',
        ];
        if($replace===true){
            echo $this->colorMsg('請輸入最後登入起始日期(ex. 2020-10-01):');
            $ltime_begin = trim(fgets(STDIN));

            $data['startTime']=$ltime_begin;
            $data['endTime']='2020-10-31';
            unset($ltime_begin);
        }
        $url = $this->setting['url'] . 'member/index/';
        // var_dump($this->setting['headers']);
        $response = $this->curl_send_new($url, $data, 'GET', $this->setting['headers']);
        $dom      = new \DOMDocument('1.0', 'UTF-8');

        @$dom->loadHTML($response);
        // print_r($dom->getElementsByTagName('tr')[22]);
        // exit;
        if (isset($dom) && isset($dom->getElementsByTagName('tr')[22])) {
            $ex_textcontent = $dom->getElementsByTagName('tr')[22]->textContent;

            preg_match('/\d+/', $ex_textcontent, $matchs);
            // print_r($matchs);
            if (isset($matchs[0])) {
                $member_total = $matchs[0];
                echo $this->light_alert("會員總數", $matchs[0]) . PHP_EOL;
                $total_page       = ceil($member_total / 300);
                $data['pageSize'] = '300%20%E6%9D%A1/%E9%A1%B5';
                echo $this->light_alert("總頁數", $total_page) . PHP_EOL;

                //所有頁數集合(以URL做陣列)
                for ($page = 1; $page <= $total_page; $page++) {
                    $urlArr[] = [
                        'url'  => $url . '_page-' . $page . '-' . $member_total . '_',
                        'Page' => $page,
                    ];
                }
                $sql_fun = function ($tem_data, $index) use ($date_convert, $replace) {
                    $agent_data = [];
                    $dom        = new \DOMDocument('1.0', 'UTF-8');
                    @$dom->loadHTML($tem_data);
                    $tmp_table = $dom->getElementsByTagName('table')[1];
                    for ($i = 1; $i < ($tmp_table->getElementsByTagName('tr')->length); $i++) {
                        $tmp_tr = $tmp_table->getElementsByTagName('tr')[$i];
                        // print_r($tmp_tr->getElementsByTagName('td')[1]);
                        if (!is_null($tmp_tr) && isset($tmp_tr->getElementsByTagName('td')[1]) && isset($tmp_tr->getElementsByTagName('td')[1]->getElementsByTagName('font')[0])) {
                            $userid = $tmp_tr
                                ->getElementsByTagName('td')[1]
                                ->getElementsByTagName('font')[0]
                                ->textContent;
                        } else {
                            continue;
                        }
                        /***************ID延伸請求***************/

                        /***************************************/
                        //帳號與ID用ID拆出帳號
                        $useracc = trim(str_replace($userid, '', $tmp_tr->getElementsByTagName('td')[1]->textContent)); //帳號

                        $username     = trim($tmp_tr->getElementsByTagName('td')[2]->textContent); // 姓名
                        $status       = (trim($tmp_tr->getElementsByTagName('td')[3]->textContent) == '停用') ? 1 : 0; //狀態
                        $frozen       = (trim($tmp_tr->getElementsByTagName('td')[4]->textContent) == '冻结') ? 0 : 1; //凍結
                        $upperagent   = (trim($tmp_tr->getElementsByTagName('td')[11]->textContent)); //代理
                        $registed_at  = date("Y-m-d H:i:s", strtotime(trim($tmp_tr->getElementsByTagName('td')[14]->textContent))); //註冊時間 美東
                        $lastlogin_at = (trim(str_replace('登陆日志', '', $tmp_tr->getElementsByTagName('td')[13]->textContent)) != "") ? $date_convert(trim(str_replace('登陆日志', '', $tmp_tr->getElementsByTagName('td')[13]->textContent))) : "";
                        $level        = trim(str_replace('调整分层', '', $tmp_tr->getElementsByTagName('td')[5]->textContent));
                        $balance      = trim(str_replace('明细', '', $tmp_tr->getElementsByTagName('td')[6]->textContent));
                        $payout       = trim($tmp_tr->getElementsByTagName('td')[8]->textContent);
                        $remark       = trim($tmp_tr->getElementsByTagName('td')[15]->textContent);
                        // $lastlogin_at = trim($date_convert)

                        $member_data = [
                            'userid'       => $userid, // 原後台ID
                            'useracc'      => $useracc, // 帳號
                            'username'     => $username, // 姓名
                            'balance'      => $balance, //主钱包余额
                            'status'       => $status, // 狀態
                            'frozen'       => $frozen, // 凍結
                            'upperagent'   => $upperagent, // 代理
                            'registed_at'  => $registed_at, // 註冊時間
                            'level'        => $level, // 會員組(分層))
                            'payout'       => $payout, // 輸贏
                            'lastlogin_at' => $lastlogin_at,
                            'remark'       => $remark,
                        ];

                        $this->db_insert("tmp_" . $this->setting['prefix'] . "_account", $member_data, $replace);
                        $this->count_test++;
                        printf("匯入數量: %s\r", $this->count_test);
                    }
                };

                $header = [
                    // "Referer"=>"123545646"
                ];

                $curl_data = [
                    'url'     => $urlArr,
                    'data'    => $data,
                    'headers' => [],
                    'method'  => 'GET',
                    'resFun'  => $sql_fun,
                    'begen'   => false,
                ];
                $this->Guzz_curl($urlArr, $data, $header, 'GET', $sql_fun, false, true);
            } else {
                echo "\e請求失敗!e[0m" . PHP_EOL;
                exit;
            }
        } else {
            if ($this->TG_login()) {
                $this->new_member_list($start, $end);
                echo "123";
            } else {
                echo "\e[1;31;43m重新登入失敗!e[0m" . PHP_EOL;
                exit;
            }
        }

    }
    public function TG_login($Reload = false)
    {
        echo $this->light_alert('登入中。。。');
        $url = $this->setting['url'];
        // 登入頁先取得Cookie值
        $data = [];
        $res  = $this->curl_send_new($url, $data, 'GET', []);
        // print_r($res);
        preg_match('/line=(.*);/', $res, $cookie_line1);
        preg_match('/randomYes=(.*);/', $res, $cookie_randomYes);
        if (!is_null($cookie_randomYes) && isset($cookie_randomYes[1]) && !is_null($cookie_line1) && isset($cookie_line1[1])) {
            foreach ($this->setting['headers'] as $key => &$value) {
                if (preg_match('/Cookie/', $value)) {
                    $value = "Cookie: line={$cookie_line1[1]}; randomYes={$cookie_randomYes[1]}";
                    break;
                }
            }
            $this->updateTojson($this->setting, self::SETTING_FILE);
            $data = [
                'username' => $this->user,
                'password' => 'qwe123',
            ];
            $res = $this->curl_send_new($url, $data, 'POST', $this->setting['headers']);
            if (preg_match('/管理员 后台/', $res)) {
                echo $this->light_alert("登入成功", "開始更新Cookie") . PHP_EOL;
                // 取跳轉後cookie
                if (preg_match('/302 Found/', $res) && preg_match('/Set-Cookie/', $res)) {
                    preg_match('/randomYes=(.*);/', $res, $cookie_randomYes2);
                    if (!is_null($cookie_randomYes2) && isset($cookie_randomYes2[1])) {
                        //原本line值加上登入後的randomYes值
                        $CookieTag = false;
                        foreach ($this->setting['headers'] as $key => $value) {
                            if (preg_match('/Cookie/', $value)) {
                                // var_dump($this->setting['headers'][$key]);
                                $this->setting['headers'][$key] = "Cookie: line={$cookie_line1[1]}; randomYes={$cookie_randomYes[1]}";
                                echo $this->light_alert("更新最新Cookie成功", "Cookie: line={$cookie_line1[1]}; randomYes={$cookie_randomYes[1]}") . PHP_EOL;
                                // exit;
                                $CookieTag = true;
                                break;
                            }
                        }
                        if (!$CookieTag) {
                            array_push($this->setting['headers'], "Cookie: line={$cookie_line1[1]}; randomYes={$cookie_randomYes[1]}");
                            echo $this->light_alert("新增Cookie至headers陣列", "Cookie: line={$cookie_line1[1]}; randomYes={$cookie_randomYes[1]}") . PHP_EOL;
                            // var_dump($this->setting['headers']);
                        }

                        // 回寫json;
                        $this->updateTojson($this->setting, self::SETTING_FILE);
                        // exit;
                        return true;
                    }
                }
            }
        }
        echo "\e[1;31;43m登入失敗，Cookie參數缺少! \e[0m" . PHP_EOL;
        print_r($res);
        return false;
    }

    public function PlantformGet($type = "")
    {
        // if($PLcount>34{

        // }
        $this->data_count = 0;
        $data             = [];

        // $ttyy = $this->type;
        $ttyy = $this->PlatformTypeArr[rand(0, 36)];
        if ($type != "") {
            $ttyy = $type;
        }
        $member_id = $this->db_data($this->db_con['agency'], "SELECT `userid` FROM tmp_h28_account WHERE $ttyy is NULL");
        $urlArr    = [];
        // print_r($member_id);
        // exit;
        foreach ($member_id as $key => $value) {
            $urlArr[] = [
                'url'    => $this->setting['url'] . 'member/balance/' . $value['userid'] . '?stype=' . $ttyy,
                'userid' => $value['userid'],
                'stype'  => $ttyy,
            ];
        }
        $count = 0;
        $total = count($urlArr);
        echo $this->light_alert("未更新" . $ttyy . "資料庫會員數:", $total);
        $sql_fun = function ($tmp_data, $member) use (&$count, $total) {

            if (!is_null($tmp_data) && is_numeric($tmp_data) && $tmp_data !== false) {
                $member_data = [
                    $member['stype'] => (float) $tmp_data,
                ];
                $count++;
                printf("数量:%d\r", ($count));
                if ($tmp_data > 0) {
                    echo PHP_EOL . "$tmp_data" . PHP_EOL;
                }
                $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $member['userid']], $member_data);
                // 交互執行
                if ($count > 300 || $count == $total) {
                    $this->PlantformGet();
                }

            } else {
                // echo "[{$index['userid']}] ERROR: {$index['stype']} - trim($tmp_data)".trim($tmp_data).PHP_EOL;
                print_r($tmp_data);
                exit;
            }

        };
        if (count($urlArr) > 0) {
            echo "开始请求!!" . PHP_EOL;
            $this->Guzz_curl($urlArr, $data, $header = [], 'GET', $sql_fun, false);

        } else {
            $count++;
            $this->PlantformGet();
        }

    }
    public function up_member_balence2()
    {
        $this->checkLogin();
        $this->data_count = 0;
        $data             = [];
        $sql_fun          = function ($tmp_data, $index) {
            // print_r($tmp_data);
            // exit;
            if (!is_null($tmp_data) && is_numeric($tmp_data) && $tmp_data !== false) {
                $member_data = [
                    $index['stype'] => (float) $tmp_data,
                ];
                $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $index['userid']], $member_data);
                printf("\e[K%s Ok!\r", $index['stype']);
            } else {
                // echo "[{$index['userid']}] ERROR: {$index['stype']} - trim($tmp_data)".trim($tmp_data).PHP_EOL;
            }
        };
        $member_arr = $this->db_data($this->db_con['agency'], "SELECT * FROM `tmp_" . $this->setting['prefix'] . "_account` WHERE `check_ccl` = 0");
        // $member_arr = $this->db_data($this->db_con['agency'], "SELECT * FROM `tmp_h28_account` WHERE (`SP` or `SS` or `BT` or `FY` or `TT` or `XG` or `VR` or `AG` or `BB` or `BB` or `AB` or `DS` or `BV` or `SG` or `EB` or `OP` or `PT` or `PM` or `HB` or `YG` or `PS` or `CQ` or `MP` or `DB` or `PP` or `SE` or `RG` or `VG` or `KY` or `LE` or `AI` or `FH` or `HT` or `JJ` or `CL` or `HL` or `AK`) is Null");

        echo $this->light_alert('未更新余额資料庫會員數:', count($member_arr)).PHP_EOL;
        foreach ($member_arr as $key => $member) {
            $member_balance_url = $this->setting['url'] . 'member/balance/' . $member['userid'];
            $response           = $this->curl_send($member_balance_url, $data, 'GET');
            // $response->getBody()->getContents();
            // var_dump($response);
            // exit;
            printf("\e[K数量:%d\r", ($key + 1));
            preg_match_all('/noBalance.+id="([A-Z]+)">[\n\s ]+((\d+)\.?\d*)/', $response, $out);
            preg_match_all('/getBalances\(\'(.+?)\'.*(点击显示|读取中|刷新重试|点击重试)/', $response, $err);
            preg_match_all('/balance" id="(.*)">[\n\s ]+读取中/', $response, $err2);

            // continue;
            foreach ($out[1] as $key2 => $stype) {
                $member_data = [
                    $stype      => $out[2][$key2],
                    'check_ccl' => '1',
                ];
                // print_r($member_data);

                $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $member['userid']], $member_data);

            }

            //重新抓取
            $data = [];
            foreach ($err[1] as $stype) {
                $data[] = [
                    'stype'  => $stype,
                    'userid' => $member['userid'],
                ];
            }
            if (count($err2[1]) > 0) {
                foreach ($err2[1] as $stype) {
                    $data[] = [
                        'stype'  => $stype,
                        'userid' => $member['userid'],
                    ];
                }
            }
            // print_r($data);
            // continue;
            $platformUrl = $this->setting['url'] . 'member/balance/' . $member['userid'];
            $this->Guzz_curl($platformUrl, $data, $header = [], 'GET', $sql_fun, false);

            // continue;

        }

    }

    public function new_member_detail()
    {}

    public function new_agent_list()
    {}
}
$start   = date("Y-m-d H:i:s");
$user    = isset($argv[1]) ? $argv[1] : "";
$Crawler = new Crawler($user);
$Crawler->index();
$end = date("Y-m-d H:i:s");

// dump("開始時間" . $start);
// dump("結束時間" . $end);