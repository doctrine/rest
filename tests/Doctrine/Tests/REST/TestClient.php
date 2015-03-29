<?php

namespace Doctrine\Tests\REST;

use Doctrine\REST\Client\Client;
use Doctrine\REST\Client\Request;

class TestClient extends Client
{
    public $last;

    public function execute(Request $request)
    {
        $url = $request->getUrl();
        $method = $request->getMethod();
        $parameters = $request->getParameters();
        $username = $request->getUsername();
        $password = $request->getPassword();

        $this->last = get_defined_vars();

        if ($url === 'http://api.people.com/article.xml') {
            if ($method === Client::PUT) {
                return array('id' => 1, 'title' => 'test');
            } else {
                if ($method === Client::POST) {
                    return $parameters;
                } else {
                    if ($method === Client::GET) {
                        return array(
                            'article' => array(
                                array(
                                    'id' => 1,
                                    'title' => 'test1'
                                ),
                                array(
                                    'id' => 2,
                                    'title' => 'test2'
                                )
                            )
                        );
                    }
                }
            }
            return array();
        } else {
            if ($url === 'http://api.people.com/article/1.xml') {
                if ($method === Client::DELETE) {
                    return array('id' => 1, 'title' => 'test');
                } else {
                    if ($method === Client::GET) {
                        return array('id' => 1, 'title' => 'test');
                    }
                }
            }
        }
        return array();
    }
}
