<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//随机生成英文单词
function get_random_word($min_length, $max_length)
{
    $word = '';

    $dictionary = ROOT_PATH . '/var/englist.txt';

    $fp = @fopen($dictionary, 'r');
    if (!$fp) {
        return false;
    }
    $size = filesize($dictionary);
    $rand_location = rand(0, $size);
    fseek($fp, $rand_location);
    //获取一个长度介于$min_length和$max_length之间，并且不包含'
    while ((strlen($word) < $min_length) || (strlen($word) > $max_length) || (strstr($word, "'"))) {
        if (feof($fp)) { //如果查找单词的文件尾，就从头开始
            fseek($fp, 0);
        }
        $word = fgets($fp, 80); //因为第一次可能获取到单词的一部分，所以使用两次fgets
        $word = fgets($fp, 80);
    }
    $word = trim($word);
    return $word;
}

//生成12位助记词
function make_walletWords()
{
    $words = array();
    for ($i = 0; $i < 12; $i++) {
        $word = get_random_word(3, 8);
        if ($word == false) {
            return false;
        }
        $words[$i] = $word;
    }
    $words_string = "";
    for ($i = 0; $i < 12; $i++) {
        $words_string .= $words[$i] . " ";
    }
    $words_string = substr($words_string, 0, strlen($words_string) - 1);
    return strtolower($words_string);
}

// API返回封装
function show($code = 1, $data = [], $msg = '', $httpCode = 200)
{

    $return_data = [
        'code' => '-1',
        'msg' => '未定义消息',
        'data' => $data ? $data : [],
    ];

    $return_data['code'] = $code;

    if ($msg !== '') {
        $return_data['msg'] = $msg;
    } else {
        if (isset(config('codeMsg')[$code])) {
            $return_data['msg'] = config('codeMsg')[$code];
        }
    }
    
    return json($return_data, $httpCode);
}

//获取服务器13位时间戳
function get13TimeStamp()
{
    list($t1, $t2) = explode(' ', microtime());
    return $t2 . ceil($t1 * 1000);
}
//密码设置
function setPassword($password)
{
    return md5($password . config('app.password_pre_halt'));
}

//引用生成分类树
function generateTree($array)
{
    //第一步 构造数据
    $items = array();
    foreach ($array as $value) {
        $items[$value['catId']] = $value;
    }
    //第二部 遍历数据 生成树状结构
    $tree = array();
    //遍历构造的数据
    foreach ($items as $key => $value) {
        //如果parentId这个节点存在
        if (isset($items[$value['parentId']])) {
            //把当前的$value放到parentId节点的children中
            $items[$value['parentId']]['hasChildren'] = 1;
            $items[$value['parentId']]['children'] = [];
        } else {
            if (!$items[$key]['catClass']) {
                $tree[] = &$items[$key];
            }
        }
    }
    return $tree;
}

//删除单个图片文件方法
function deleteImageFile($fileDir)
{
    $filename = ROOT_PATH . 'public' . DS . $fileDir;
    if (file_exists($filename)) {
        unlink($filename);
        return true;
    } else {
        return true;
    }
}

//美金转人民币
function USD2RMB($amount)
{
    //百度汇率接口（15分钟左右更新一次）
    $url = 'https://www.binance.com/gateway-api/v2/public/ocbs/fiat-channel-gateway/get-quotation?fiatCode=CNY&cryptoAsset=USDT';
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
   
    $content = objtoarr(json_decode($data));
    try {
        if ($content['data']['price']) {
            $rate =  bcdiv(1, $content['data']['price'], 2);
        } elseif ($content['data']['priceVoList'][0]['price']) {
            $rate = bcdiv(1, $content['data']['priceVoList'][0]['price'], 2);
        } else {
            $rate = 6.87; //默认汇率
        }
    } catch (\Exception $e) {
        $rate = 6.87; //默认汇率
    }
    $rmb = $amount * $rate;
    return $rmb;
}

/**
 * 获取美金汇率
 *
 * @param  [type] $amount
 * @return void
 */
function USDRate()
{
    //百度汇率接口（15分钟左右更新一次）
    $url = 'https://www.binance.com/gateway-api/v2/public/ocbs/fiat-channel-gateway/get-quotation?fiatCode=CNY&cryptoAsset=USDT';
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据

    $content = objtoarr(json_decode($data));
    try {
        if ($content['data']['price']) {
            $rate =  bcdiv(1, $content['data']['price'], 2);
        } elseif ($content['data']['priceVoList'][0]['price']) {
            $rate = bcdiv(1, $content['data']['priceVoList'][0]['price'], 2);
        } else {
            $rate = 6.87; //默认汇率
        }
    } catch (\Exception $e) {
        $rate = 6.87; //默认汇率
    }
    return $rate;
}

/**
 * 人民币转美金
 *
 * @param  $amount
 * @return float|int
 */
function RMB2USD($amount)
{
    //百度汇率接口（15分钟左右更新一次）
    $url = 'https://www.binance.com/gateway-api/v2/public/ocbs/fiat-channel-gateway/get-quotation?fiatCode=CNY&cryptoAsset=USDT';
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    $content = objtoarr(json_decode($data));
  
    try {
        if ($content['data']['price']) {
            $rate =  bcdiv(1, $content['data']['price'], 2);
        } elseif ($content['data']['priceVoList'][0]['price']) {
            $rate = bcdiv(1, $content['data']['priceVoList'][0]['price'], 2);
        } else {
            $rate = 6.87; //默认汇率
        }
    } catch (\Exception $e) {
        $rate = 6.87; //默认汇率
    }
    $usd = $amount / $rate;
    return $usd;
}

function sctonum($num, $double = 5)
{
    if (false !== stripos($num, "e")) {
        $a = explode("e", strtolower($num));
        return rtrim(rtrim(bcmul($a[0], bcpow(10, $a[1], $double), $double), '0'), '.');
    }
    return $num;
}

function time2date($time)
{
    return $time == 0 ? "-" : date("Y-m-d H:i:s", $time);
}

function getMillisSecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectimes = substr($msectime, 0, 13);
}
function version_int($versionStr)
{
    $version_int = explode('.', $versionStr);
    $str = '';
    foreach ($version_int as $v) {
        $str .= $v;
    }
    return (int) $str;
}

function check_array_unique($data)
{
    if (count($data) != count(array_unique($data))) {
        return true;
    }
    return false;
}
function objtoarr($obj)
{

    $ret = array();

    foreach ($obj as $key => $value) {
        if (gettype($value) == 'array' || gettype($value) == 'object') {
            $ret[$key] = objtoarr($value);
        } else {
            $ret[$key] = $value;
        }
    }

    return $ret;
}

function cateTree($arr, $idName, $pidName, $pid = 0, $level = 0, $html = '      |--')
{
    if (empty($arr)) {
        return array();
    }
    $icon = ['│', '├', '└'];
    $result = array();
    $total  = count($arr);
    $number = 1;
    foreach ($arr as $val) {
        $tmp_str = str_repeat($icon[0] . '   ', $level > 0 ? $level - 1 : 0);
        if ($total == $number) {
            $tmp_str .= $icon[2];
        } else {
            $tmp_str .= $icon[1];
        }
        if ($val[$pidName] == $pid) {
            $val['level'] = $level + 1;
            $val['html'] = '   ' . ($level == 0 ? '   ' : '   ' . $tmp_str . "   ");
            $result[] = $val;
            $result = array_merge($result, cateTree($arr, $idName, $pidName, $val[$idName], $val['level'], $html));
        }
        $number++;
    }
    return $result;
}

function mylog($log_content)
{

    $max_size = 300000000000;
    $log_filename = RUNTIME_PATH . date('Ym-d') . ".log";
    if (file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)) {
        rename($log_filename, dirname($log_filename) . '/' . date('Ym-d-His') . ".log");
    }
 
    file_put_contents($log_filename, $log_content, FILE_APPEND);
}

function builderRand($num = 10)
{
    return substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $num);
}

//
function getEveryday($day_number = 7)
{
    $seven_day = date("Y-m-d", strtotime("-" . $day_number . " day"));
    $stimestamp = strtotime($seven_day);
    $etimestamp = strtotime(date("Y-m-d", time()));
    // 计算日期段内有多少天
    $days = ($etimestamp - $stimestamp) / 86400 + 1;
    // 保存每天日期
    $date = array();
    for ($i = 0; $i < $days; $i++) {
        $date[] = date('m-d', $stimestamp + (86400 * $i));
    }
    return $date;
}

function getEveryweek($week_number = 7)
{
    $data = [];
    for ($i = 0; $i < $week_number; $i++) {
        $sdefaultDate = date("Y-m-d", strtotime(date("Y-m-d") . "-" . ($i  * 7) . " day"));
      
        $first = 1;
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        //本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        $arr = [
            $week_start,
            $week_end
        ];
        array_push($data, $arr);
    }
       
    return $data;
}

function getEverymonth($month_number = 7)
{
    $data = [];
    $tmp_mon = date("m");
    $tmp_year = date("Y");
    for ($i = 0; $i < $month_number; $i++) {
        $tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - $i, 1, $tmp_year);
        $y = date("Y", $tmp_forwardmonth);
        $m = date("m", $tmp_forwardmonth);
        $arr =  mFristAndLast($y, $m);
        array_push($data, $arr);
    }
       
    return $data;
}

function mFristAndLast($y = 0, $m = 0)
{
    $y = $y ? $y : date('Y');
    $m = $m ? $m : date('m');
    $d = date('t', strtotime($y . '-' . $m));
    return array(date("Y-m-d", strtotime($y . '-' . $m)),date("Y-m-d", mktime(23, 59, 59, $m, $d, $y)));
}

function my_dir($dir)
{
    $files = array();
    if (@$handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != ".") {
                if (is_dir($dir . "/" . $file)) { //递归字文件夹
                    $files[$file] = my_dir($dir . "/" . $file);
                } else { //不然就将文件的名字存入数组；
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}

function findNum($str = '')
{

    $int = (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    return $int;
}

function makeDir($new_files)
{
    if (!file_exists($new_files)) {
        //检查是否有该文件夹，如果没有就创建，并给予最高权限
        //服务器给文件夹权限
        mkdir($new_files, 0777, true);
    }
}


function posturl($url, $data, $headerArray)
{
    $data  = json_encode($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return json_decode($output, true);
}


function geturl($url)
{
    $headerArray = array("Content-type:application/json;","Accept:application/json");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output, true);
    return $output;
}

function randMobile()
{
    //手机号2-3为数组
    $numberPlace = array(30,31,32,33,34,35,36,37,38,39,50,51,58,59,89);
   
     $mobile = 1;
     $mobile .= $numberPlace[rand(0, count($numberPlace) - 1)];
     $mobile .= str_pad(rand(0, 99999999), 8, 0, STR_PAD_LEFT);
  
    return $mobile;
}

function hideStr($strs)
{
        $strLen = mb_strlen($strs);
        ($strLen - 2) > 0 ? $len = $strLen - 2 :  $len = 1;
        $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= '*';
    }
        $touStr = mb_substr($strs, 0, 1);
        $strLen == 2 ? $weiStr = '' : $weiStr = mb_substr($strs, -1, 1);
        $strs  = $touStr . $str . $weiStr;
        return $strs;
}


function wordTime($time)
{
    $time = (int) substr($time, 0, 10);
    $int = time() - $time;
    $str = '';
    if ($int <= 30) {
        $str = sprintf('刚刚', $int);
    } elseif ($int < 60) {
        $str = sprintf('%d秒前', $int);
    } elseif ($int < 3600) {
        $str = sprintf('%d分钟前', floor($int / 60));
    } elseif ($int < 86400) {
        $str = sprintf('%d小时前', floor($int / 3600));
    } elseif ($int < 2592000) {
        $str = sprintf('%d天前', floor($int / 86400));
    } elseif ($int > 2592000) {
        $str = sprintf('%d月前', floor($int / 2592000));
    } else {
        $str = date('Y-m-d H:i:s', $time);
    }
    return $str;
}
