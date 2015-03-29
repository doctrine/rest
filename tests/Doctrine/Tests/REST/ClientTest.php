<?php

namespace Doctrine\Tests\REST;

use Doctrine\REST\Client\Client;
use Doctrine\REST\Client\Entity;
use Doctrine\REST\Client\Manager;
use Doctrine\Tests\DoctrineTestCase;

class ClientTest extends DoctrineTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = new TestClient();

        $manager = new Manager($this->client);
        $manager->registerEntity('Doctrine\Tests\REST\ClientArticleTest');
        $manager->registerEntity('Doctrine\Tests\REST\Status');

        Entity::setManager($manager);
    }

    /**
     * @test
     *
     * @covers Doctrine\REST\Client\Entity
     * @covers Doctrine\REST\Client\EntityConfiguration
     * @covers Doctrine\REST\Client\Manager
     * @covers Doctrine\REST\Client\ResponseTransformer\AbstractResponseTransformer
     * @covers Doctrine\REST\Client\URLGenerator\AbstractURLGenerator
     * @covers Doctrine\REST\Client\URLGenerator\StandardURLGenerator
     */
    public function testGetPath()
    {
        $this->assertEquals(
            'http://api.people.com/article/1.xml',
            ClientArticleTest::generateUrl(array('id' => 1))
        );

        $this->assertEquals(
            'http://api.people.com/article/1/test.xml',
            ClientArticleTest::generateUrl(array('id' => 1, 'action' => 'test'))
        );

        $this->assertEquals(
            'http://api.people.com/article.xml',
            ClientArticleTest::generateUrl()
        );

        $this->assertEquals(
            'http://api.people.com/article/test.xml',
            ClientArticleTest::generateUrl(array('action' => 'test'))
        );

        $this->assertEquals(
            'http://api.people.com/article.xml?test=test',
            ClientArticleTest::generateUrl(array('parameters' => array('test' => 'test')))
        );
    }

    public function testInsert()
    {
        $test = new ClientArticleTest();
        $test->setTitle('testing');
        $test->save();

        $this->assertEquals(1, $test->getId());
        $this->assertEquals('http://api.people.com/article.xml', $this->client->last['url']);
        $this->assertEquals('PUT', $this->client->last['method']);
    }

    public function testUpdate()
    {
        $test = new ClientArticleTest();
        $test->setId(1);
        $test->setTitle('test');
        $test->save();

        $this->assertEquals('test', $test->getTitle());
        $this->assertEquals('http://api.people.com/article/1.xml', $this->client->last['url']);
        $this->assertEquals('POST', $this->client->last['method']);
    }

    public function testDelete()
    {
        $test = new ClientArticleTest();
        $test->setId(1);
        $test->delete();

        $this->assertEquals('http://api.people.com/article/1.xml', $this->client->last['url']);
        $this->assertEquals('DELETE', $this->client->last['method']);
    }

    public function testFind()
    {
        $test = ClientArticleTest::find(1);

        $this->assertEquals('test', $test->getTitle());
        $this->assertEquals('http://api.people.com/article/1.xml', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testFindWithAction()
    {
        $test = ClientArticleTest::find(1, 'test');

        $this->assertEquals('http://api.people.com/article/1/test.xml', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testFindAll()
    {
        $test = ClientArticleTest::findAll();
        $this->assertEquals(2, count($test));

        $one = $test[0];
        $two = $test[1];

        $this->assertEquals(1, $one->getId());
        $this->assertEquals('test1', $one->getTitle());

        $this->assertEquals(2, $two->getId());
        $this->assertEquals('test2', $two->getTitle());

        $this->assertEquals('http://api.people.com/article.xml', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testFindAllWithAction()
    {
        $test = ClientArticleTest::findAll('test');

        $this->assertEquals('http://api.people.com/article/test.xml', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testFindAllWithParameters()
    {
        $test = ClientArticleTest::findAll(null, array('test' => 'test'));

        $this->assertEquals('http://api.people.com/article.xml?test=test', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testExecute()
    {
        $test = ClientArticleTest::execute(Client::GET, 'test', array('test' => 'test'));

        $this->assertEquals('http://api.people.com/article/test.xml?test=test', $this->client->last['url']);
        $this->assertEquals('GET', $this->client->last['method']);
    }

    public function testTwitterStatus()
    {
        $test = Status::execute(Client::POST, 'update', array('status' => 'updating my status'));

        $this->assertEquals('http://twitter.com/statuses/update.xml', $this->client->last['url']);
        $this->assertEquals('POST', $this->client->last['method']);
        $this->assertEquals(array('status' => 'updating my status'), $this->client->last['parameters']);
        $this->assertEquals('username', $this->client->last['username']);
        $this->assertEquals('password', $this->client->last['password']);
    }
}
