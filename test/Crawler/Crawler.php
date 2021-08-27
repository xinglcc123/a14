 <?php
// namespace App\Console\Commands;
require_once "./vendor/autoload.php";
// require_once "./VerificationCodeHelper.php";
//http://quanmingadmin888.com 為+8時區
date_default_timezone_set("Asia/Taipei");
include './class.php';
set_time_limit(0); //程式執行時間無限制
// $setting = new setting();
class Crawler extends setting
{
    // const UPDATE_WHEN_SECOND = 4800; // 更新登入时间多久以前内的资料 (单位:秒)
    // const AUTO_UPDATE_WAIT   = 600; //自动更新秒数(单位:秒)
    const TOTAL_MEMBER       = 195171 ;
    const PAGE_SIZE          = 1000;
    protected $setting;
    protected $user = "";
    protected $class;
    protected $CrawlerData = [
        'user_id' => '173',
        'ip'      => '13.78.85.146',
    ];

    public function __construct()
    {
        parent::__construct();

        // 第一次登入用
        // $data = [
        //     'user_id' => '173',
        //     'token'   => 'sNdfM_iGfV3FKE7gpG29WfJ3AaAyAzDy',
        //     'ip'      => '13.78.85.146',
        // ];
        // $url = $this->setting['url'] . 'apis/user/data';
        // $response = $this->curl_send_new($url, $data, 'POST', $this->setting['headers']);
    }
    public function index($process = null)
    {
        if (is_null($process)) {
            echo $this->colorMsg("[ 1=>'A14主页', 2=>'登入时间更新会员主页' 3=>'会员厅室余额余额' 4=>'会员详情(总存提) 9=>自动化更新']" . PHP_EOL . "請輸入:", "INPUT");
            $handle  = fopen("php://stdin", "r");
            $process = trim(fgets($handle));
            fclose($handle);
        } else {
            $process = $process;
        }
        switch ($process) {
            case '1':
                $this->new_member_list_vvn(); //用户列表
                break;
            case '3':
                $this->memberGamePlantFormBalance(); //厅式余额
                break;
            case '2': // 输入登入时间更新并更新所有细项
                $this->new_member_list_vvn(true);
                $this->memberGamePlantFormBalance(2);
                $this->memberDetail(1); //有姓名
                $this->memberDetail(2); //无姓名
                break;
            case '4':
                $this->memberDetail(1);
                $this->memberDetail(2);
                break;
            case '5':
                $this->new_agent_list();
                break;
            case '9':
                $AUTOBEGIN = microtime(true);
                echo $this->colorMsg('自动化开始, 更新' . self::UPDATE_WHEN_SECOND . ' 秒前到现在有登入的会员，延迟5秒开始', 'MSG');
                sleep(5);
                $this->save_csv('AutoRunLog.csv', 'AUTOBEGIN', date("Y-m-d H:i:s"));
                $AutoUpdateTime = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s")) - self::UPDATE_WHEN_SECOND);
                $this->new_member_list_vvn(true, $AutoUpdateTime);
                $this->save_csv('AutoRunLog.csv', '【' . date("Y-m-d H:i:s") . '】 ClASS new_member_list_vvn OK USE TIME: ' . (round((microtime(true)) - $AUTOBEGIN, 3)) . ' 秒');
                $this->memberGamePlantFormBalance(2);
                $this->save_csv('AutoRunLog.csv', '【' . date("Y-m-d H:i:s") . '】 ClASS memberGamePlantFormBalance OK USE TIME: ' . (round((microtime(true)) - $AUTOBEGIN, 3)) . ' 秒');
                $this->memberDetail(2);
                $this->save_csv('AutoRunLog.csv', '【' . date("Y-m-d H:i:s") . '】 ClASS memberDetail OK USE TIME: ' . (round((microtime(true)) - $AUTOBEGIN, 3)) . ' 秒');
                // sleep(5);
                $AUTOEND = microtime(true);
                $this->save_csv('AutoRunLog.csv', 'AUTOEND', date('Y-m-d H:i:s'), 'USE TIME: ' . (round($AUTOEND - $AUTOBEGIN, 3)) . ' 秒');
                echo $this->colorMsg('【' . date("Y-m-d H:i:s") . '】下一次自动更新等待时间: ' . self::AUTO_UPDATE_WAIT . '秒', '');
                sleep(self::AUTO_UPDATE_WAIT);
                $this->index(9);
                break;
            default:
                echo "請輸入正確數字" . PHP_EOL;
                $this->index();
                break;
        }
    }
    // 會員資料抓取
    public function new_member_list_vvn(bool $replace = false, $replaceTime = null)
    {

        $sql_fun     = function ($tmp_data, $index) use ($replace, $replaceTime) {
            $tmp_decode = json_decode($tmp_data, true);
            var_dump($tmp_decode);
            // return 'success';
        };
        $pageSize    = self::PAGE_SIZE;
        $totalMember = self::TOTAL_MEMBER;
        $totalPage   = ceil($totalMember / $pageSize);

        // 页数阵列
        // for ($i = 1; $i <= $totalPage; $i++) {
        //     $page_array[] = [
        //         'page' => $i,
        //     ];
        // }
        $data = [
          'oldName' => 'qqaaccdd2',
          'type' => 'checkUserOnline'
        ];
        $url = $this->setting['url'].'agent/agent/';
        $response = $this->curl_send_new($url, $data, 'POST', $sql_fun, $this->setting['header']);

        exit;

        for ($i = 1; $i <= $totalPage; $i++) {
            $data = [
                'order' => 'create_time',
                'type' => '1',
                'id' => '',
                'rate' => '5',
                'bool' => '5',
                'status' => '',
                'pageNo' => $i,
                'pageSize' => '1000',
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
            ];
            $url = $this->setting['url'].'agent/AccountServlet/';
            $response = $this->curl_send_new($url, $data, 'POST', $sql_fun, $this->setting['headers']);
        }
        // print_r($page_array);
        exit;

        $BEGIN    = microtime(true);
        $response = $this->Guzz_curl_new($url, $data, 'POST', $sql_fun, $page_array);
        $END      = microtime(true);
        echo $this->colorMsg($needUpCount . '筆 線程數=>' . $this->setting['concurrency'] . ' 耗費時間:' . round(($END - $BEGIN), 3) . '秒 ', 'WARMING');






        echo '[1]'.$response.PHP_EOL;






        exit;
















        if ($replace && is_null($replaceTime)) {
            $replaceTime = '';
            echo $this->colorMsg('请输入更新哪个时间点后的会员 (ex. 2020-10-28 10:10:00)', "INPUT");
            echo ":";
            $replaceTime = trim(fgets(STDIN));
            if (!strtotime($replaceTime)) {
                echo $this->colorMsg('时间格式错误!', 'ERROR');
                exit;
            }
        }
        $needUpCount = 0;
        $sql_fun     = function ($tmp_data, $index) use ($replace, $replaceTime, &$needUpCount) {
            $tmp_decode = json_decode($tmp_data, true);
            foreach ($tmp_decode['data']['list'] as $key => $tmpArr) {
                if (!is_null($tmpArr) && $tmpArr && isset($tmpArr['id'])) {
                    if ($replace) {
                        // 最后登入时间戳大于更新时间时间戳
                        if ($replaceTime == '' || $replaceTime === false || $tmpArr['login_time'] < strtotime($replaceTime)) {
                            continue;
                        } else {
                            echo "【{$tmpArr['username']} UPDATE】" . PHP_EOL;
                            $needUpCount++;
                        }
                    }
                    $userid            = $tmpArr['id']; // 原後台ID
                    $useracc           = $tmpArr['username']; // 帳號
                    $balance           = $tmpArr['price']; //主钱包余额
                    $registed_at       = date("Y-m-d H:i:s", $tmpArr['created_time']); // 注册时间
                    $lastlogin_at      = date("Y-m-d H:i:s", $tmpArr['login_time']); //最后登入时间
                    $upperagent        = $tmpArr['parent']; //上层代理
                    $upperagent_userid = $tmpArr['parent_id']; //上层代理ID
                    $username          = $tmpArr['alias']; //真实姓名
                    $remark            = $tmpArr['content']; // 备注
                    $level             = $tmpArr['hierarchy']; //用户层级(会员组)
                    $viplevel          = $tmpArr['grade']; //VIP等级 VIP0 = grade => 1
                    switch ($tmpArr['status']) {
                        case '1':
                            $status = '1';
                            $frozen = '0';
                            break;
                        case '-1':
                            $status = '0';
                            $frozen = '0';
                            break;
                        case '-2':
                            $status = '1';
                            $frozen = '1';
                            break;
                    }

                    $member_data = [
                        'userid'            => $userid, // 原後台ID
                        'useracc'           => $useracc, // 帳號
                        'username'          => $username, // 姓名
                        'balance'           => $balance, //主钱包余额
                        'status'            => $status, // 狀態
                        'frozen'            => $frozen, // 凍結
                        'upperagent'        => $upperagent, // 代理
                        'upperagent_userid' => $upperagent_userid, // 代理
                        'registed_at'       => $registed_at, // 註冊時間
                        'level'             => $level, // 會員組(分層))
                        'viplevel'          => $viplevel, // VIP等级 VIP0 = grade => 1
                        'lastlogin_at'      => $lastlogin_at,
                        'remark'            => $remark,
                    ];
                    $this->db_insert("tmp_" . $this->setting['prefix'] . "_account", $member_data, $replace);
                    if (!$replace) {
                        $needUpCount++;
                    }
                    printf("\e[K更新数:%s\r", $needUpCount);
                } else {
                    throw new Exception('Json解析失败');
                }
            }

        };
        $page        = 1;
        $pageSize    = self::PAGE_SIZE;
        $totalMember = self::TOTAL_MEMBER;
        $totalPage   = ceil($totalMember / $pageSize);

        // 页数阵列
        // for ($i = 1; $i <= $totalPage; $i++) {
        //     $page_array[] = [
        //         'page' => $i,
        //     ];
        // }






        // print_r($page_array);
        // exit;
        $data = [
            'way'          => '1',
            'page'         => '1',
            'page_number'  => '50',
            'hierarchy_id' => '1',
            'type'         => '',
            'online'       => '',
            'name'         => '',
            'admin_id'     => '',
            'username'     => '',
            'status'       => '',
            'user_id'      => $this->CrawlerData['user_id'],
            'start_time'   => '',
            'end_time'     => '',
            'parent_id'    => '',
            'ip'           => $this->CrawlerData['ip'],
        ];
        $url      = $this->setting['url'] . 'apis/admin/list';
        $BEGIN    = microtime(true);
        $response = $this->Guzz_curl_new($url, $data, 'POST', $sql_fun, $page_array);
        $END      = microtime(true);
        echo $this->colorMsg($needUpCount . '筆 線程數=>' . $this->setting['concurrency'] . ' 耗費時間:' . round(($END - $BEGIN), 3) . '秒 ', 'WARMING');
    }

    // 厅式余额
    public function memberGamePlantFormBalance($case = null)
    {
        $url = $this->setting['url'] . 'apis/admin/game';
        $sql = "SELECT `userid` AS id FROM `tmp_" . $this->setting['prefix'] . "_account` WHERE `check_ccl` = '0' ";
        if (is_null($case)) {
            echo $this->colorMsg('请输入更新条件 => 【1: All 2:姓名不为空的 3: 登入时间_____以后 4.登入时间_____以后 且姓名不为空】', 'INPUT');
            $case = (int) trim(fgets(STDIN));
        }
        switch ($case) {
            case 1: //All
                $sql .= "";
                break;
            case 2: //姓名不为空的
                $sql .= " AND `username` != '' ";
                break;
            case 3: //登入时间_____以后
            case 4: //登入时间_____以后 且姓名不为空
                echo $this->colorMsg('请输入更新的时间点(ex.2020-10-27 12:00:00)', 'INPUT');
                $updateTime = trim(fgets(STDIN));
                if (strtotime($updateTime)) {
                    $sql .= " AND `lastlogin_at` > '" . $updateTime . "' ";
                } else {
                    echo $this->colorMsg('时间格式错误，请重新输入', 'ERROR');
                    unset($sql);
                    unset($case);
                    $this->memberGamePlantFormBalance(3);
                }
                if ($case == 4) {
                    $sql .= " AND `username` != '' ";
                }
                break;
            default:
                echo $this->colorMsg('选项错误，请重新选择!', 'ERROR');
                unset($sql);
                unset($case);
                $this->memberGamePlantFormBalance();
                break;
        }
        $sql .= " ORDER BY `lastlogin_at` DESC";
        echo $this->colorMsg($sql, 'MSG');
        $memberArray = $this->db_data($this->db_con['agency'], $sql);
        $data        = [];
        foreach ($memberArray as $key => $member) {
            $data[] = [
                'ip'       => $this->CrawlerData['ip'],
                'admin_id' => $member['id'],
                'user_id'  => $this->CrawlerData['user_id'],
            ];
        }
        echo $this->colorMsg("尚未更新餘額會員筆數: " . count($data), 'MSG');
        $this->setting['concurrency'] = '3';
        echo $this->colorMsg("線程數: " . $this->setting['concurrency'], 'MSG');
        $count   = 0;
        $sql_fun = function ($tmp_data, $index) use (&$count) {
            $tmp_decode = json_decode($tmp_data, true);
            if ($tmp_decode && $tmp_decode['msg'] == '成功') {
                $member_balance = $tmp_decode['data']['list'];
                if (count($member_balance) > 0) {
                    foreach ($member_balance as $key => $game) {
                        switch ($game['name']) {
                            case '电子游戏':
                                $table = 'EGAME';
                                break;
                            case '开元棋牌':
                                $table = 'KY';
                                break;
                            case 'VG棋牌':
                                $table = 'VG';
                                break;
                            case '美天棋牌':
                                $table = 'MT';
                                break;
                            case '幸运棋牌':
                                $table = 'LK';
                                break;
                            case 'GM棋牌':
                                $table = 'GM';
                                break;
                            case '财神棋牌':
                                $table = 'CS';
                                break;
                            case '真人棋牌':
                                $table = 'DG';
                                break;
                            case '电子竞技':
                                $table = 'ESPORT';
                                break;
                            default:
                                echo $this->colorMsg('平台异常:'.$game['name'], 'WARMING');
                                break;
                        }

                        if(isset($game['money']) && is_numeric($game['money'])){
                            $member_data[$table] = $game['money'];
                        }
                    }
                        $member_data['check_ccl'] = '1';

                }
                // var_dump($member_data);
                if(isset($member_data) && count($member_data) > 0){
                    $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $index['admin_id']], $member_data);
                    $count++;
                    printf("\e[K更新数:%s\r", $count);
                }else{
                    echo $this->colorMsg($index['admin_id']. '无资料可以更新', 'WARMING');
                }
            }
        };
        $BEGIN = microtime(true);
        $this->Guzz_curl_new($url, $data, 'POST', $sql_fun);
        $END = microtime(true);
        echo $this->colorMsg($count . '筆 線程數=>' . $this->setting['concurrency'] . ' 耗費時間:' . round(($END - $BEGIN), 3) . '秒 ', 'WARMING');
        // exit;

    }

    // 会员详情 check_d_w
    public function memberDetail($case = '1')
    {
        switch ($case) {
            case '1': // 有姓名的依照最后登入时间更新
                $memberArray = $this->db_data($this->db_con['agency'], "SELECT `userid` AS id FROM `tmp_" . $this->setting['prefix'] . "_account` WHERE `check_d_w` = '0' AND `username` != '' ORDER BY `lastlogin_at` DESC");
                break;
            case '2': // 单纯依照最后更新时间做更新
            default:
                $memberArray = $this->db_data($this->db_con['agency'], "SELECT `userid` AS id FROM `tmp_" . $this->setting['prefix'] . "_account` WHERE `check_d_w` = '0' ORDER BY `lastlogin_at` DESC");
                break;
        }
        $data = [];
        foreach ($memberArray as $key => $member) {
            $data[] = [
                'ip'       => $this->CrawlerData['ip'],
                'admin_id' => $member['id'],
                'user_id'  => $this->CrawlerData['user_id'],
            ];
        }
        if (count($data) == 0) {
            echo $this->colorMsg('无会员需要更新', 'ERROR');
            exit;
        }
        $upCount = 0;
        $sql_fun = function ($tmp_data, $index) use (&$upCount) {
            $tmp_decode = json_decode($tmp_data, true);
            if ($tmp_decode && $tmp_decode['msg'] == '成功') {
                // var_dump($tmp_decode);
                // exit;
                $memberBank = $tmp_decode['data']['list']['bank'];
                $memberData = $tmp_decode['data']['list']['data'];

                $member_data = [];
                //bank
                if (count($memberBank) > 0) {
                    $member_data['bank']     = $memberBank[0]['bank_name'];
                    $member_data['banknum']  = $memberBank[0]['bank_number'];
                    $member_data['bankuser'] = $memberBank[0]['username'];
                    $member_data['city']     = $memberBank[0]['city'];
                    $member_data['province'] = $memberBank[0]['province'];
                    if (isset($memberBank[1])) {
                        foreach ($memberBank as $key => $bankData) {
                            if ($key == 0) {continue;};
                            $this->save_csv('vvnLog_userBankOther.csv', $memberBank[$key]['admin_id'], $memberBank[$key]['username'] . ',' . $memberBank[$key]['bank_name'] . ',' . $memberBank[$key]['bank_number'] . ',' . $memberBank[$key]['city'] . ',' . $memberBank[$key]['province']);
                        }
                    }
                }
                // data
                $member_data['deposit']          = $memberData['in_money_price']; // 总存
                $member_data['deposit_count']    = $memberData['in_money_number']; // 总存次数
                $member_data['withdrawal']       = $memberData['out_money_price']; //总提
                $member_data['withdrawal_count'] = $memberData['out_money_number']; //总提次数
                $member_data['payout']           = $memberData['all']; //输赢
                $member_data['activebet']        = $memberData['lottery_price']; //有效投注(打码量)
                //Tag
                $member_data['check_d_w'] = '1';

                $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $index['admin_id']], $member_data);
                $upCount++;
                printf("\e[K\e[1;34m更新数:%s\r", $upCount);
                // $this->db_update("tmp_" . $this->setting['prefix'] . "_account", ['userid' => $member['admin_id']], $member_data);
            }

        };
        $this->setting['concurrency'] = 10;
        $url                          = $this->setting['url'] . 'apis/admin/edit';
        $BEGIN                        = microtime(true);
        $this->Guzz_curl_new($url, $data, 'POST', $sql_fun);
        $END = microtime(true);
        echo $this->colorMsg($upCount . '筆 線程數=>' . $this->setting['concurrency'] . ' 耗費時間:' . round(($END - $BEGIN), 3) . '秒 ', 'WARMING');

    }
}
$start = date("Y-m-d H:i:s");
// $user    = isset($argv[1]) ? $argv[1] : "";
$Crawler = new Crawler();
$Crawler->index();
$end = date("Y-m-d H:i:s");

// dump("開始時間" . $start);
// dump("結束時間" . $end);