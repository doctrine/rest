<?php

use Doctrine\REST\Server\Server,
    Doctrine\Common\ClassLoader;

require '/Users/jwage/Sites/doctrine2git/lib/Doctrine/Common/ClassLoader.php';

$classLoader = new ClassLoader('Doctrine\REST', __DIR__ . '/lib');
$classLoader->register();

$classLoader = new ClassLoader('Doctrine', '/Users/jwage/Sites/doctrine2git/lib');
$classLoader->register();

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setProxyDir('/tmp');
$config->setProxyNamespace('Proxies');
$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

$connectionOptions = array(
  'driver' => 'pdo_mysql',
  'dbname' => 'rest_test',
  'user' => 'root'
);

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

$parser = new \Doctrine\REST\Server\PHPRequestParser();
$requestData = $parser->getRequestArray();

class TestAction
{
    public function executeDBAL()
    {
        return array('test' => 'test');
    }
}

$server = new \Doctrine\REST\Server\Server($em->getConnection(), $requestData);
$server->addEntityAction('user', 'test', 'TestAction');
$server->execute();
$server->getResponse()->send();