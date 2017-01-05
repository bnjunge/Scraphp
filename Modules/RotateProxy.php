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
use Scraphp\ScraphpCore;

/**
 * Class RotateProxy
 * @package Scraphp\Modules
 */
class RotateProxy extends ScraphpCore
{
    /**
     * Using for PDO instant injection
     * @var
     */
    protected $db;
    /**
     * MySQL table name for current working project.
     * @var string
     */
    public $db_table = 'scraphp_ips';

    /**
     * RotateProxy constructor.
     * @param $db
     */
    public function __construct($db)
    {
        parent::__construct();

        // PDO instant injection
        $this->db = &$db;

        try {
            // Check if table exists or not
            $sql = "SELECT 1 FROM {$this->db_table} LIMIT 1";
            $this->db->query($sql);

        } catch (\Exception $e) {
            // if table doesn't exist in database, create a temporary table.
            $sql = <<<EOF
CREATE TABLE `{$this->db_table}` (
  `proxy_id` int(10) UNSIGNED NOT NULL,
  `proxy_ip` varchar(40) NOT NULL,
  `proxy_type` tinyint(2) UNSIGNED NOT NULL,
  `proxy_port` smallint(5) UNSIGNED NOT NULL,
  `proxy_username` varchar(24) NOT NULL,
  `proxy_password` varchar(24) NOT NULL,
  `proxy_calls` mediumint(8) UNSIGNED NOT NULL,
  `proxy_last_call_time` int(10) UNSIGNED NOT NULL,
  `proxy_last_check_time` int(10) UNSIGNED NOT NULL,
  `proxy_last_url` varchar(255) NOT NULL,
  `proxy_agent` varchar(255) NOT NULL,
  `proxy_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->db_table}`
  ADD PRIMARY KEY (`proxy_id`),
  ADD UNIQUE KEY `proxy_ip` (`proxy_ip`),
  ADD KEY `proxy_last_call_time` (`proxy_last_call_time`);

ALTER TABLE `{$this->db_table}`
  MODIFY `proxy_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
EOF;

            $this->db->query($sql);

            $this->debug_message('notice', __METHOD__ . ": {$this->db_table} is created successfully.");
        }
    }

    /**
     * Get all proxy IPs data
     * @return mixed
     */
    public function get_all_ips()
    {
        $sql = "SELECT * FROM {$this->db_table}";
        $result = $this->db->query($sql);
        $rows = $result->fetchAll();
        return $rows;
    }

    /**
     * Get single proxy IP data.
     *
     * @param string $ip
     * @return bool or array
     */
    public function get_ip($ip)
    {
        $sql = "SELECT * FROM {$this->db_table} WHERE proxy_ip = '{$ip}'";
        $result = $this->db->query($sql);
        $row = $result->fetch(\PDO::FETCH_ASSOC);

        if (!empty($row['proxy_ip'])) {
            return $row;
        } else {
            $this->debug_message('notice', __METHOD__ . ": {$ip} not found.");
            return false;
        }


    }

    /**
     * Get last called proxy IP data
     * @return bool or array
     */
    public function get_last_called_ip()
    {
        $sql = "SELECT * FROM {$this->db_table} ORDER BY proxy_last_call_time LIMIT 1";
        $result = $this->db->query($sql);
        $row = $result->fetch(\PDO::FETCH_ASSOC);

        if (!empty($row['proxy_ip'])) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Update single proxy IP data
     *
     * @param array $arr_data
     * @param array $arr_where
     * @return null
     */
    public function update_ip($arr_data, $arr_where)
    {
        $tmp_set = array();
        $tmp_where = array();
        foreach ($arr_data as $key => $value) {
            $data[] = $value;
            $tmp_set[] = "{$key}=?";
        }
        foreach ($arr_where as $key => $value) {
            $tmp_where[] = "{$key}=?";
        }
        $str_set = implode(',', $tmp_set);
        $where_set = implode(' AND ', $tmp_where);

        $run_sql = $this->db->prepare("UPDATE {$this->db_table} SET {$str_set} WHERE {$where_set}");
        $run_sql->execute($data);
        return null;
    }

    /**
     * Add a proxy IP data to the list.
     *
     * @param array $arr_data
     * @return null
     */
    public function add_ip($arr_data)
    {
        $proxy_ip = '';
        foreach ($arr_data as $key => $value) {
            $data[] = $value;
            $tmp_set[] = "`{$key}`";
            $tmp_value[] = "?";

            if ($key == 'proxy_ip') {
                $proxy_ip = $value;
            }
        }
        $str_set = implode(',', $tmp_set);
        $str_value = implode(',', $tmp_value);

        $run_sql = $this->db->prepare("INSERT INTO {$this->db_table} ({$str_set}) VALUES({$str_value})");
        if ($run_sql->execute($data)) {
            $this->debug_message('notice', __METHOD__ . ": {$proxy_ip} is added to the list.");
        }
        return null;
    }

    /**
     * Remove a proxy IP from the list.
     *
     * @param string $ip
     */
    public function remove_ip($ip)
    {
        $sql = "DELETE FROM {$this->db_table} WHERE proxy_ip = '{$ip}'";
        $run_sql = $this->db->prepare($sql);
        if ($run_sql->execute()) {
            $this->debug_message('notice', __METHOD__ . ": {$ip} is removed from the list.");
        }
    }

    /**
     * Import proxy IPs from a text file.
     *
     * Note that the content format is ip:port:username:password per line.
     * i.g:
     * 195.25.17.111:8080:terrylin:12345678
     * 195.25.17.111:8080 (without username and password)
     *
     * @param string $file_path
     * @param string $proxy_type
     * @return array
     */
    public function import_ips($file_path, $proxy_type = 'http')
    {
        $proxies = array();

        if (!empty($file_path)) {
            if (file_exists($file_path)) {
                $proxies_file = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $proxies = array();

                foreach ($proxies_file as $key => $value) {

                    $tmp = explode(':', $value);

                    $data['proxy_ip'] = $tmp[0];
                    $data['proxy_port'] = isset($tmp[1]) ? $tmp[1] : '';
                    $data['proxy_username'] = isset($tmp[2]) ? $tmp[2] : '';
                    $data['proxy_password'] = isset($tmp[3]) ? $tmp[3] : '';

                    if ($proxy_type == 'http') {
                        $data['proxy_type'] = CURLPROXY_HTTP;
                    }
                    if ($proxy_type == 'sock4') {
                        $data['proxy_type'] = CURLPROXY_SOCKS4;
                    }
                    if ($proxy_type == 'sock5') {
                        $data['proxy_type'] = CURLPROXY_SOCKS5;
                    }

                    if ($this->get_ip($data['proxy_ip'])) {
                        // nothing to do
                    } else {
                        $this->add_ip($data);
                        $proxies[$key] = $value;
                    }
                }
            }
        }
        $count_proxy = count($proxies);

        if ($count_proxy > 0) {
            $this->debug_message('notice', __METHOD__ . ": {$count_proxy} proxy IPs are imported to the list successfully.");
        } else {
            $this->debug_message('notice', __METHOD__ . ": Nothing happened. IPs already exist.");
        }
        return $proxies;
    }
}
