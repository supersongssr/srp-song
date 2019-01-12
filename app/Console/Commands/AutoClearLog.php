<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Http\Models\SsNodeIp;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\SsNodeTrafficDaily;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserTrafficLog;
use App\Http\Models\UserTrafficHourly;
use Illuminate\Console\Command;
use Log;

class AutoClearLog extends Command
{
    protected $signature = 'autoClearLog';
    protected $description = '自动清除日志';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        // 清除日志
        if (self::$systemConfig['is_clear_log']) {
            $this->clearLog();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 清除日志
    private function clearLog()
    {
        // 自动清除30分钟以前的节点负载信息日志
        SsNodeInfo::query()->where('log_time', '<=', strtotime("-30 minutes"))->delete();

        // 自动清除1小时以前的节点在线用户数日志
        SsNodeOnlineLog::query()->where('log_time', '<=', strtotime("-60 minutes"))->delete();

        // 自动清除30天以前的用户流量日志
        //Song 3天以前
        UserTrafficLog::query()->where('log_time', '<=', strtotime("-3 days"))->delete();

        // 自动清除10天以前的用户每小时流量数据日志
        UserTrafficHourly::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-10 days')))->delete();

        // 自动清除60天以前的节点每小时流量数据日志
        //Song 10天以前的清除
        SsNodeTrafficHourly::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-10 days')))->delete();

        // 自动清除90天以前的节点每天流量数据日志
        //Song ７天以前
        SsNodeTrafficDaily::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-７ days')))->delete();

        // 自动清除30天以前用户封禁日志
        UserBanLog::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-30 days")))->delete();

        // 自动清除30天以前用户连接IP
        SsNodeIp::query()->where('created_at', '<=', strtotime("-30 days"))->delete();

        // 自动清除30天以前用户登陆日志
        UserLoginLog::query()->where('created_at', '<=', date('Y-m-d H:i:s', strtotime("-30 days")))->delete();
    }

}
