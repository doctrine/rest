<?php

namespace Doctrine\REST\Server;

class PHPRequestParser
{
    public function getRequestArray()
    {
        $path = $_SERVER['PATH_INFO'];
        $path = ltrim($path, '/');
        $e = explode('/', $path);
        $count = count($e);
        $end = end($e);
        $e2 = explode('.', $end);
        $e[count($e) - 1] = $e2[0];
        $format = isset($e2[1]) ? $e2[1] : 'xml';
        $entity = $e[0];
        $id = isset($e[1]) ? $e[1] : null;
        $action = isset($e[2]) ? $e[2] : null;
        $method = isset($_REQUEST['_method']) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD'];
        $method = strtoupper($method);

        if ($count === 1) {
            if ($method === 'POST' || $method === 'PUT') {
                $action = 'insert';
            } else if ($method === 'GET') {
                $action = 'list';
            }
        } else if ($count === 2) {
            if ($method === 'POST' || $method === 'PUT') {
                $action = 'update';
            } else if ($method === 'GET') {
                $action = 'get';
            } else if ($method === 'DELETE') {
                $action = 'delete';
            }
        } else if ($count === 3) {
            $action = $action;
        }

        $data = array_merge(array(
            '_entity' => $entity,
            '_id' => $id,
            '_action' => $action,
            '_format' => $format
        ), $_REQUEST);

        return $data;
    }
}