<?php

namespace Doctrine\Tests\REST;

use Doctrine\ORM\EntityManager;
use Doctrine\REST\Client\Client;
use Doctrine\REST\Client\Request;
use Doctrine\REST\Server\Server;

class TestFunctionalClient extends Client
{
    public $name;
    public $source;
    public $data = array();
    public $count = 0;

    public function __construct($name, $source)
    {
        $this->name = $name;
        $this->source = $source;
    }

    public function execServer(Request $request, $requestArray, $parameters = array()/*, $responseType = 'xml'*/)
    {
        $requestArray = array_merge($requestArray, (array) $parameters);
        $server = new Server($this->source, $requestArray);
        if ($this->source instanceof EntityManager) {
            $server->setEntityAlias('Doctrine\Tests\REST\DoctrineUser', 'user');
        }
        $response = $server->getRequestHandler()->execute();
        $data = $request->getResponseTransformerImpl()->transform($response->getContent());
        return $data;
    }

    public function execute(Request $request)
    {
        $url = $request->getUrl();
        $method = $request->getMethod();
        $parameters = $request->getParameters();
        $responseType = $request->getResponseType();
        $server = null;

        // GET api/user/1.xml (get)
        if ($method === 'GET' && preg_match_all('/api\/' . $this->name . '\/([0-9]).xml/', $url, $matches)) {
            $id = $matches[1][0];
            $server = $this->execServer($request, array(
                    '_method' => $method,
                    '_format' => $responseType,
                    '_entity' => $this->name,
                    '_action' => 'get',
                    '_id' => $id
                ), $parameters, $responseType);
        }

        // GET api/user.xml (list)
        if ($method === 'GET' && preg_match_all('/api\/' . $this->name . '.xml/', $url, $matches)) {
            $server = $this->execServer($request, array(
                    '_method' => $method,
                    '_format' => $responseType,
                    '_entity' => $this->name,
                    '_action' => 'list'
                ), $parameters, $responseType);
        }

        // PUT api/user.xml (insert)
        if ($method === 'PUT' && preg_match_all('/api\/' . $this->name . '.xml/', $url, $matches)) {
            $server = $this->execServer($request, array(
                    '_method' => $method,
                    '_format' => $responseType,
                    '_entity' => $this->name,
                    '_action' => 'insert'
                ), $parameters, $responseType);
        }


        // POST api/user/1.xml (update)
        if ($method === 'POST' && preg_match_all('/api\/' . $this->name . '\/([0-9]).xml/', $url, $matches)) {
            $server = $this->execServer($request, array(
                    '_method' => $method,
                    '_format' => $responseType,
                    '_entity' => $this->name,
                    '_action' => 'update',
                    '_id' => $parameters['id']
                ), $parameters, $responseType);
        }

        // DELETE api/user/1.xml (delete)
        if ($method === 'DELETE' && preg_match_all('/api\/' . $this->name . '\/([0-9]).xml/', $url, $matches)) {
            $server = $this->execServer($request, array(
                    '_method' => $method,
                    '_format' => $responseType,
                    '_entity' => $this->name,
                    '_action' => 'delete',
                    '_id' => $matches[1][0]
                ), $parameters, $responseType);
        }
        return $server;
    }
}
