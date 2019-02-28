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
use think\Session;

use app\index\model\Mould;

function check()
{
	$user = Session::get('user');
	if(empty($user))
	{
		//echo url('index/index/login');exit;
		header('location:' . url('index/index/login'));exit;
	}
}

//获取左侧菜单
function getLeftMenu()
{
    $mould = Mould::where('sort','>', 0)->select();
    return $mould;
}


/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string  $name 字符串
 * @param integer $type 转换类型
 * @param bool    $ucfirst 首字母是否大写（驼峰规则）
 * @return string
 */
function parseName($name, $type = 0, $ucfirst = true)
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);
        return $ucfirst ? ucfirst($name) : lcfirst($name);
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}


function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
 if(function_exists("mb_substr")){
 if($suffix)
  return mb_substr($str, $start, $length, $charset)."...";
 else
  return mb_substr($str, $start, $length, $charset);
 }
 elseif(function_exists('iconv_substr')) {
 if($suffix)
  return iconv_substr($str,$start,$length,$charset)."...";
 else
  return iconv_substr($str,$start,$length,$charset);
 }
 $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
 $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
 $re['gbk']  = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
 $re['big5']  = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
 preg_match_all($re[$charset], $str, $match);
 $slice = join("",array_slice($match[0], $start, $length));
 if($suffix) return $slice."…";
 return $slice;
}

function time_tran($the_time){
	//$now_time = date("Y-m-d H:i:s",time()+8*60*60);

   	//$now_time = strtotime($now_time);
	$now_time = time();
   	$show_time = $the_time;
   	$dur = $now_time - $show_time;
	if($dur < 0){
		return $the_time;
   	}elseif($dur < 60){
		return $dur.'秒前';
	}elseif($dur < 3600){
		return floor($dur/60).'分钟前';
	}elseif($dur < 86400){
		return floor($dur/3600).'小时前';
	}elseif($dur < 259200){//3天内
        return floor($dur/86400).'天前';
	}else{
        return date('n月j日',$the_time);
    }
}

function makeradio($arr,$name, $class ,$value = -1)
{
    $carr =array(
        array('rdio-default','radioDefault'),
        array('rdio-primary','radioPrimary'),
        array('rdio-warning','radioWarning'),
        array('rdio-success','radioSuccess'),
        array('rdio-danger','radioDanger'),
    );
    $html = '';
    $i=0;
    foreach ($arr as $key=>$val)
    {
        $checked = '';
        if($i == 0 && $value < 0)
        {
            $checked = 'checked';
        }elseif($value == $key){
            $checked = 'checked';
        }
        $html.= '<div class="rdio '.$carr[$i][0].' '.$class.'">
                  <input type="radio" name="'.$name.'" value="'.$key.'" id="'.$carr[$i][1].'" '.$checked.'>
                  <label for="'.$carr[$i][1].'">'.$val.'</label>
             </div>';
        $i++;
    }
    return $html;
}


