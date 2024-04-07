<?php

namespace Zwping\DcatAdmin\SysInfo\Http\Widgets\Cards;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Metrics\Card;
use Dcat\Admin\Widgets;

/** PhpInfo
 * @date: 2024-03-28 @zwping
 */
class PhpInfo extends Card {

    protected function init() {
        parent::init();

        // $this->title("Phpinfo");
        $this->content($this->getPhpinfo2());

//         $c = Admin::color()->alpha('primary', 0.3);
//         Admin::style(<<<CSS
// .phpinfo-v {
// overflow-x: auto;
// word-wrap: break-word;
// word-break: break-all;
// }
// .box-header {
// background-color: {$c};
// }
// CSS);
//         $boxs = [];
//         foreach($this->getPhpinfo() as $classify => $info) {
//             $vs = [];
//             $maxvslen = 1;
//             foreach($info->toArray() as $k => $v) {
//                 $vs[] = tap(array_merge(is_int($k) ? [] : [$k], is_string($v) ? [$v] : $v), function($_v) use(&$maxvslen) {
//                     $maxvslen = count($_v) > $maxvslen ? count($_v) : $maxvslen;
//                 });
//             }
//             foreach($vs as &$v) {
//                 $v = array_pad($v, $maxvslen, '');
//             }
//             $table = Widgets\Table::make([], $vs, "table-striped thead-dark table-hover table-sm phpinfo-v");
//             $boxs[] = Widgets\Box::make($classify, $table)->style('solid')->collapsable()->render();
//         }
//         $this->content(implode('', $boxs));
    }


    public function getPhpinfo() {
        ob_start();

        // $what = static::config('what', INFO_ALL);
        $what = INFO_ALL;
        phpinfo($what);

        $phpinfo = ['phpinfo' => collect()];

        if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {

            collect($matches)->each(function ($match) use (&$phpinfo) {
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = collect();

                } elseif (isset($match[3])) {
                    $keys = array_keys($phpinfo);

                    $phpinfo[end($keys)][$match[2]] = isset($match[4]) ? collect([$match[3], $match[4]]) : $match[3];
                } else {
                    $keys = array_keys($phpinfo);

                    $phpinfo[end($keys)][] = $match[2];
                }
            });
        }

        ob_end_clean();

        return collect($phpinfo);
    }

    private function getPhpinfo2() {
        ob_start();
        phpinfo(INFO_ALL);
        $phpinfo = ob_get_clean();
        if (ob_get_length()) {
            ob_end_clean();
        }
        return $phpinfo;
    }

}