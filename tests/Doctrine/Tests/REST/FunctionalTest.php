<?php

namespace Doctrine\Tests\REST;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\REST\Client\Manager;
use Doctrine\REST\Client\Entity;
use Doctrine\Tests\DoctrineTestCase;

class FunctionalTest extends DoctrineTestCase
{
    private $manager;
    private $client;

    /**
     * @param $type
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function setUpRest($type)
    {
        $config = new Configuration();
        $config->setMetadataCacheImpl(new ArrayCache);
        $config->setProxyDir('/tmp');
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

        $connectionOptions = array(
          'driver' => 'pdo_sqlite',
          'memory' => true
        );

        $em = EntityManager::create($connectionOptions, $config);
        $classes = array($em->getMetadataFactory()->getMetadataFor('Doctrine\Tests\REST\DoctrineUser'));

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        if ($type === 'orm') {
            $this->client = new TestFunctionalClient('user', $em);
        } else {
            $this->client = new TestFunctionalClient('user', $em->getConnection());
        }

        $this->manager = new Manager($this->client);
        $this->manager->registerEntity('Doctrine\Tests\REST\User');

        Entity::setManager($this->manager);
    }

    /**
     * @test
     *
     * @covers Doctrine\REST\Client\Entity
     * @covers Doctrine\REST\Client\EntityConfiguration
     * @covers Doctrine\REST\Client\Manager
     * @covers Doctrine\REST\Client\Request
     * @covers Doctrine\REST\Client\ResponseTransformer\AbstractResponseTransformer
     * @covers Doctrine\REST\Client\ResponseTransformer\StandardResponseTransformer
     * @covers Doctrine\REST\Client\URLGenerator\AbstractURLGenerator
     * @covers Doctrine\REST\Client\URLGenerator\StandardURLGenerator
     * @covers Doctrine\REST\Server\Action\AbstractAction
     * @covers Doctrine\REST\Server\Action\DeleteAction
     * @covers Doctrine\REST\Server\Action\GetAction
     * @covers Doctrine\REST\Server\Action\InsertAction
     * @covers Doctrine\REST\Server\Action\ListAction
     * @covers Doctrine\REST\Server\Action\UpdateAction
     * @covers Doctrine\REST\Server\Request
     * @covers Doctrine\REST\Server\RequestHandler
     * @covers Doctrine\REST\Server\Response
     * @covers Doctrine\REST\Server\Server
     */
    public function testOrm()
    {
        $this->setUpRest('orm');
        $this->internalActiveRecordApiTest();
    }

    /**
     * @test
     *
     * @covers Doctrine\REST\Client\Entity
     * @covers Doctrine\REST\Client\EntityConfiguration
     * @covers Doctrine\REST\Client\Manager
     * @covers Doctrine\REST\Client\Request
     * @covers Doctrine\REST\Client\ResponseTransformer\AbstractResponseTransformer
     * @covers Doctrine\REST\Client\ResponseTransformer\StandardResponseTransformer
     * @covers Doctrine\REST\Client\URLGenerator\AbstractURLGenerator
     * @covers Doctrine\REST\Client\URLGenerator\StandardURLGenerator
     * @covers Doctrine\REST\Server\Action\AbstractAction
     * @covers Doctrine\REST\Server\Action\DeleteAction
     * @covers Doctrine\REST\Server\Action\GetAction
     * @covers Doctrine\REST\Server\Action\InsertAction
     * @covers Doctrine\REST\Server\Action\ListAction
     * @covers Doctrine\REST\Server\Action\UpdateAction
     * @covers Doctrine\REST\Server\Request
     * @covers Doctrine\REST\Server\RequestHandler
     * @covers Doctrine\REST\Server\Response
     * @covers Doctrine\REST\Server\Server
     */
    public function testDbal()
    {
        $this->setUpRest('dbal');
        $this->internalActiveRecordApiTest();
    }

    private function internalActiveRecordApiTest()
    {
        $user1 = new User();
        $user1->setUsername('jwage');
        $user1->save();

        $this->assertEquals(1, $user1->getId());

        $user2 = new User();
        $user2->setUsername('fabpot');
        $user2->save();

        $this->assertEquals(2, $user2->getId());

        $user3 = new User();
        $user3->setUsername('romanb');
        $user3->save();

        $this->assertEquals(3, $user3->getId());

        $user3->setUsername('romanb_new');
        $user3->save();

        $user3test = User::find($user3->getId());
        $this->assertEquals('romanb_new', $user3test->getUsername());

        $test = User::findAll();
        $this->assertEquals(3, count($test));
        $this->assertTrue($user1 === $test[0]);
        $this->assertTrue($user2 === $test[1]);
        $this->assertTrue($user3 === $test[2]);

        $user3->delete();

        $test = User::findAll();

        $this->assertEquals(2, count($test));
    }
}
