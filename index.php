<?php

/*
 * Copyright (c) 2011 Toni Spets <toni.spets@iki.fi>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
 
set_include_path(get_include_path() . PATH_SEPARATOR . 'include/');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('CnCNet_');
unset($loader);

$db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => 'db/cncnet.db'));
Zend_Db_Table::setDefaultAdapter($db);

$db->query('PRAGMA foreign_keys = ON');
unset($db);

$type = isset($_GET['type']) ? $_GET['type'] : 'json';

if ($type == 'json' || $type == 'jsonp') {
    $server = new Zend_Json_Server();
    $server->setClass('CnCNet_Api');

    if ($type == 'jsonp') {
        $server->setRequest(new CnCNet_Json_Server_Request_Http_Jsonp());
        $server->setResponse(new CnCNet_Json_Server_Response_Http_Jsonp());
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            echo $server->setEnvelope(Zend_Json_Server_Smd::ENV_JSONRPC_2)->getServiceMap();
            return;
        }
    }

    $server->handle();
} else if ($type == 'xml') {
    header('Content-type: text/xml');
    $server = new Zend_XmlRpc_Server();
    $server->setClass('CnCNet_Api');
    echo $server->handle();
} else if ($type == 'dumb') {
    header('Content-type: text/plain; charset=UTF-8');
    $server = new CnCNet_Api();
    if (isset($_GET['method']) && method_exists($server, $_GET['method'])) {
        try {
            @$ret = call_user_func_array(array($server, $_GET['method']), isset($_GET['params']) ? (is_array($_GET['params']) ? $_GET['params'] : array($_GET['params'])) : array());
        } catch(Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo '500 Internal Server Error (Exception from API)';
            exit;
        }
        function to_dumb_string($var)
        {
            if (is_int($var) || is_float($var)) {
                return (string)$var;
            } else if (is_bool($var)) {
                return $var ? 'true' : 'false';
            } else if (is_string($var)) {
                return '"'.str_replace('"', '\\"', $var).'"';
            } else if (is_array($var)) {
                return 'INVAL';
            }
        }
        if (is_array($ret)) {
            $tmp = array();
            foreach ($ret as $v) {
                $tmp[] = to_dumb_string($v);
            }
            echo implode(',', $tmp);
        } else {
            echo to_dumb_string($ret);
        }
    }
}
