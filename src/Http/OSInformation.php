<?php

namespace Zwping\DcatAdmin\SysInfo\Http;

use Linfo\Linfo;
use Linfo\OS\OS;

/** 系统信息 */
class OSInformation {

    private OS      $osParaser;
    private array   $osInfo;

    public function __construct(array $settings = [], bool $cpuPercentage = true) {
        $linfo = new Linfo($settings);
        $this->osParaser = $linfo->getParser();
        if ($cpuPercentage && $this->osParaser instanceof \Linfo\OS\Linux) {
            $this->osParaser->determineCPUPercentage();   # sleep(1)确定cpu使用率
        }
        $this->setOsInfo();
    }

    public function getOSParaser(): OS {
        return $this->osParaser;
    }

    public function getOsInfo(): array {
        return $this->osInfo ?? [];
    }

    private function setOsInfo() {
        $mounts = collect($this->getOSMethod('Mounts'));   # 文件系统
        $this->osInfo = [
            'distro'        => collect($this->getOSMethod('Distro'))->join(''),   # 发行版本
            'cpu_usage'     => array_map(fn($it) => is_numeric($it) ? "$it%" : $it, [$this->getOSMethod('CPUUsage')])[0],   # cpu使用率 # 会执行sleep(1)
            'web_service'   => $this->getOSMethod('webService'),   # web服务信息
            'php_version'   => $this->getOSMethod('phpVersion'),   # php版本
            'os'            => $this->getOSMethod('OS'),   # 系统 PHP_OS
            'kernel'        => $this->getOSMethod('Kernel'), # 内核版本
            'hostname'      => $this->getOSMethod('Hostname', 'HostName'), # 主机名
            'model'         => $this->getOSMethod('Model'), # 型号
            // $this->getOSMethod('Net'),   # 网络设备
            'uptime'        => collect($this->getOSMethod('UpTime'))->map(fn($v, $k) => ($k == 'bootedTimestamp') ? \Carbon\Carbon::createFromTimestamp($v) : $v)->join(' . booted: '),   # 在线时间
            // $this->getOSMethod('ProcessStats'), # 进程
            'cpu_arch'      => $this->getOSMethod('CPUArchitecture'),   # cpu架构
            'cpus'          => array_map(fn($it) => 
                (empty($it['Vendor']) ? '' : "{$it['Vendor']} - ") # 供应商
                . $it['Model']                              # 型号
                . (empty($it['MHz']) ? '' : ' ('. number_format($it['MHz']/1000, 1).'GHz)')
                . (empty($it['usage_percentage']) ? '' : ' ('. $it['usage_percentage'] .'%)')
            , $this->getOSMethod('CPU')),    # cpu
            'memory'        => array_map(fn($it) => [
                'type'              => $it['type'],     # 内存类型
                'total'             => static::humanReadableSize($it['total']),    # 内存总容量
                'free'              => static::humanReadableSize($it['free']),     # 可用
                'used'              => static::humanReadableSize($it['total'] - $it['free']),     # 已用
                'used_percent'      => round(($it['total'] - $it['free'])/$it['total']*100) . '%',     # 已用百分比
                'swap_total'        => static::humanReadableSize($it['swapTotal']),    # 虚拟内存总容量 # 交换空间
                'swap_free'         => static::humanReadableSize($it['swapFree']),    # 虚拟内存 可用
                'swap_used'         => static::humanReadableSize($it['swapTotal'] - $it['swapFree']),    # 虚拟内存 已用
                'swap_used_percent' => round(($it['swapTotal'] - $it['swapFree'])/$it['swapTotal']*100) . '%',    # 虚拟内存 已用百分比
                'swaps' => array_map(fn($it1) => [
                    'device'            => $it1['device'],      # 设备
                    'type'              => $it1['type'],        # 类型
                    'size'              => static::humanReadableSize($it1['size']),        # 总容量
                    'used'              => static::humanReadableSize($it1['used']),        # 已用
                    'free'              => static::humanReadableSize($it1['size']-$it['used']),        # 可用
                    'used_percent'      => round($it1['used']/$it1['size']*100) . '%',        # 已用百分比
                ], $it['swapInfo']),  # 虚拟内存列表
            ], [$this->getOSMethod('Ram')])[0],  # 内存
            // $this->getOSMethod('Battery'),  # 电池
            'hd'            => array_map(fn($it) => [
                'device'        => $it['device'],       # 地址
                'vendor'        => $it['vendor'],       # 制作商
                'name'          => $it['name'],         # 名称
                'reads'         => $it['reads'] ?: 0,            # 读取
                'writes'        => $it['writes'] ?: 0,       # 写入
                'size'          => static::humanReadableSize($it['size']),  # 总容量
                'partitions'    => array_map(fn($it1) => $it1['name'] .' - '. static::humanReadableSize($it1['size']), $it['partitions']),    # 硬盘分区
            ], $this->getOSMethod('HD')),    # 硬盘
            // $this->getOSMethod('Virtualization'),
            // $this->getOSMethod('Load'),
            'mounts'        => [
                'total'         => [
                    'size'          => static::humanReadableSize($mounts->sum('size')),
                    'used'          => static::humanReadableSize($mounts->sum('used')),
                    'free'          => static::humanReadableSize($mounts->sum('free')),
                    'used_percent'  => round($mounts->sum('used')/$mounts->sum('size')*100) . '%',
                    'free_percent'  => round($mounts->sum('free')/$mounts->sum('size')*100) . '%',
                    ], 
                'mounts' => $mounts->map(fn($it) => [
                    'device'        => $it['device'],   // 设备
                    'mount'         => $it['mount'],    // 挂载点
                    'type'          => $it['type'],     // 文件系统
                    'size'          => static::humanReadableSize($it['size']),  // 总容量
                    'used'          => static::humanReadableSize($it['used']),  // 已用
                    'free'          => static::humanReadableSize($it['free']),  // 可用
                    'used_percent'  => ($it['used_percent'] ?: 0) .'%',     // 已用百分比
                    'free_percent'  => ($it['free_percent'] ?: 0) .'%',     // 可用百分比
                ])->toArray(),
            ],   # 文件系统
        ];
    }


    private function getOSMethod($methodName, $methodName2=''): mixed {
        try {
            $methodName = str_starts_with('get', $methodName) ? $methodName : "get{$methodName}";
            return $this->osParaser->$methodName();
        } catch (\Throwable $th) {
            try {
                if (empty($methodName2)) {
                    return 'Unknown';
                }
                $methodName2 = str_starts_with('get', $methodName2) ? $methodName2 : "get{$methodName2}";
                return $this->osParaser->$methodName2();
            } catch (\Throwable $th) {
                return 'Unknown.';
            }
        }
    }

    public static function humanReadableSize(float $sizeInBytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($sizeInBytes === 0.0) {
            return '0 '.$units[1];
        }
        for ($i = 0; $sizeInBytes > 1024; $i++) {
            $sizeInBytes /= 1024;
        }

        return round($sizeInBytes, 2).' '.$units[$i];
    }




}
