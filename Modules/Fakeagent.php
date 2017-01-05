<?php

namespace Scraphp\Modules;
/*
 *
 * Licensed under The GNU GENERAL PUBLIC LICENSE V3
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Terry Lin <terrylin.developer@gmail.com>
 * @version 1.0 ($Rev: 1 $)
 */

/*
 *  Fake UserAgent Generater
 */

class Fakeagent
{
    public $option;
    public $type;
    public $lang;
    public $ip;
    /**
     * UserAgent constructor.
     * Fake the user-agent data as your need!
     * @param array $config
     */
    public function __construct($config = array()) {
        /*
         * @param string $option : 'random', 'fixed', 'ip'
         * default: random
         */
        $this->option = (isset($config['option'])) ? $config['option'] : 'random';

        /*
         * @param string $type : 'mixed', 'web', 'mobile'
         * default: web
         */
        $this->type = (isset($config['type'])) ? $config['type'] : 'desktop';

        /*
         * @param string $lang - for example : 'en-US', 'zh-TW'
         * default: en-US
         */
        $this->lang = (isset($config['lang'])) ? $config['lang'] : 'en-US';
        
        // If you set an IP, it creates specific user-agent infomation for this IP
        $this->ip = (isset($config['ip'])) ? $config['ip'] : '';
    }

    /**
     * Return a fake user-agent information
     *
     * @return string
     */
    public function value() {
        
        if ($this->type == 'mixed') {
            $this->type = (mt_rand(1, 99) % 2 == 1) ? 'desktop' : 'mobile';
        }

        switch ($this->type) {
            case 'mobile':

                $arr_android_rom_versions = array(
                    'IML74K', 'GRJ90', 'GRI40', 'FRF91', 'FRG83D', 'FRG83'
                );

                $arr_android_versions = array(
                    '2.1', '2.2',
                    '2.3.1', '2.3.2', '2.3.3', '2.3.4', '2.3.5',
                    '4.0.1', '4.0.2', '4.0.3',
                    '4.3.1', '4.3.2', '4.3.3',
                    '4.4.1',
                    '5.0.1',
                );

                $arr_android_devices = array(
                    'HTC Sensation', 'HTC_IncredibleS_S710e', 'HTC Vision', 'HTC_Pyramid', 'HTC Legend', 'HTC_DesireZ_A7272', 'HTC_DesireS_S510e',
                    'LG-P505R', 'LG-LU3000',
                    'T-Mobile myTouch 3G Slide',

                );

                if ($this->option == 'ip' AND $this->ip != '') {
                    $ip_num = sprintf("%u", ip2long($this->ip));

                    $ipnum_android_rom_version = $ip_num % count($arr_android_rom_versions);
                    $ipnum_arr_android_version = $ip_num % count($arr_android_versions);
                    $ipnum_android_device = $ip_num % count($arr_android_devices);

                    $str_android_rom_version = $arr_android_rom_versions[$ipnum_android_rom_version];
                    $str_android_version = $arr_android_versions[$ipnum_arr_android_version];
                    $str_android_device = $arr_android_devices[$ipnum_android_device];
                    $str_applewebkit_number = '53' . $ip_num % 5 . '.' . $ip_num % 31;
                }

                if ($this->option == 'random') {
                    shuffle($arr_android_rom_versions);
                    shuffle($arr_android_versions);
                    shuffle($arr_android_devices);
                    $str_android_rom_version = $arr_android_rom_versions[0];
                    $str_android_version = $arr_android_versions[0];
                    $str_android_device = $arr_android_devices[0];
                    $str_applewebkit_number = ($this->option == 'random') ? mt_rand(530, 534) . '.' . mt_rand(1, 30) : '533.1';
                }

                if ($this->option == 'fixed') {
                    $str_android_rom_version = $arr_android_rom_versions[0];
                    $str_android_version = $arr_android_versions[0];
                    $str_android_device = $arr_android_devices[0];
                    $str_applewebkit_number = '533.1';
                }

                $user_agent_string = 'Mozilla/5.0 (Linux; U; Android ' . $str_android_version . '; ' . $this->lang . '; ' . $str_android_device . ' Build/' . $str_android_rom_version . ') AppleWebKit/' . $str_applewebkit_number . ' (KHTML, like Gecko) Version/4.0 Mobile Safari/' . $str_applewebkit_number;

                return $user_agent_string;
                break;

            case 'desktop':
            default:

                $arr_browser_types = array(
                    'Chrome',
                    'Firefox'
                );

                $arr_system_versions = array(
                    'Windows NT 5.1', 'Windows NT 6.1', 'Windows NT 6.2', 'Windows NT 6.3', 'Windows NT 6.4', 'Windows NT 10',
                    'Windows NT 6.2; WOW64', 'Windows NT 6.3; WOW64', 'Windows NT 6.4; WOW64', 'Windows NT 10; WOW64',
                    'Macintosh; Intel Mac OS X 10_10_1', 'Macintosh; Intel Mac OS X 10_9_0', 'Macintosh; Intel Mac OS X 10_9_1', 'Macintosh; Intel Mac OS X 10_9_2', 'Macintosh; Intel Mac OS X 10_9_3', 'Macintosh; Intel Mac OS X 10_8_0',
                    'X11; Linux x86_64'
                );

                if ($this->option == 'ip' AND $this->ip != '')
                {
                    $ip_num = sprintf("%u", ip2long($this->ip));

                    $ipnum_browser_types = $ip_num % count($arr_browser_types);
                    $ipnum_system_versions = $ip_num % count($arr_system_versions);

                    $str_browser_type = $arr_browser_types[$ipnum_browser_types];
                    $str_system_version = $arr_system_versions[$ipnum_system_versions];

                    if ($str_browser_type == 'Chrome') {
                        $str_browser_version = 36 + ($ip_num % 7) . '.0.22' . str_pad($ip_num % 15, 2, '0',STR_PAD_LEFT) . '.0';
                    }
                    if ($str_browser_type == 'Firefox') {
                        $str_browser_version = 27 + ($ip_num % 14) . '.' . $ip_num % 4;
                        $str_firefox_date = 20 . (10 + ($ip_num % 5)) . str_pad($ip_num % 13, 2, '0',STR_PAD_LEFT) . str_pad($ip_num % 28, 2, '0',STR_PAD_LEFT);
                    }

                }

                if ($this->option == 'random') {
                    shuffle($arr_system_versions);
                    $str_system_version = $arr_system_versions[0];

                    shuffle($arr_browser_types);
                    $str_browser_type = $arr_browser_types[0];

                    if ($str_browser_type == 'Chrome') {
                        $str_browser_version = mt_rand(36, 41) . '.0.' . mt_rand(2214, 2228) . '.0';
                    }
                    if ($str_browser_type == 'Firefox') {
                        $str_browser_version = mt_rand(27, 40) . '.' . mt_rand(0, 3);
                        $str_firefox_date = mt_rand(2010, 2014) . str_pad(mt_rand(1, 12), 2, '0',STR_PAD_LEFT) . str_pad(mt_rand(1, 28), 2, '0',STR_PAD_LEFT);
                    }
                }

                if ($this->option == 'fixed') {
                    $str_system_version = $arr_system_versions[0];
                    $str_browser_version = $arr_browser_types[0];
                    $str_firefox_date = '20100101';
                }

                if ($str_browser_type == 'Chrome') {
                    $user_agent_string = 'Mozilla/5.0 (' . $str_system_version . ') AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'. $str_browser_version .' Safari/537.36';
                }

                if ($str_browser_type == 'Firefox') {
                    $user_agent_string = 'Mozilla/5.0 (' . $str_system_version . '; rv:' . $str_browser_version . ') Gecko/' . $str_firefox_date . ' Firefox/' . $str_browser_version;
                }

                return $user_agent_string;
                break;
        }
    }
}
