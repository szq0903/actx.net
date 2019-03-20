<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/3/20
 * Time: 23:11
 */

use app\index\model\Sysinfo;
use app\index\model\MoneyLog;
use app\index\model\Member;


//检查用户余额并扣除指定金额浏览单价
function delMoneyByCateart($temp)
{
    $member =  Member::get($temp->getData('mid'));
    $sysinfo = Sysinfo::get(1);
    if($member['money'] <= $sysinfo['everyprice'] )
    {
        $this->error('发布信息用户余额不足');
    }else{
        //减去指定
        $member->money = $member->money - $sysinfo['everyprice'];
        $member->save();

        $moneylog = new MoneyLog();
        $moneylog->update = time();
        $moneylog->mid = $temp->getData('mid');
        $moneylog->money = - $sysinfo['everyprice'];
        $moneylog->msg = '浏览'.$temp['title']."减少余额";
        $moneylog->save();
    }
}