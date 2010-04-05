<?php

use Doctrine\REST\Client\Client,
    Doctrine\REST\Client\Manager,
    Doctrine\REST\Client\Entity,
    Doctrine\REST\Client\EntityConfiguration;

require '../../../../../lib/Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\REST', '../lib');
$classLoader->register();

$client = new Client();
$manager = new Manager($client);
$manager->registerEntity('Status');

Entity::setManager($manager);

class Status extends Entity
{
    public static function configure(EntityConfiguration $entityConfiguration)
    {
        $entityConfiguration->setUrl('http://api.twitter.com/1');
        $entityConfiguration->setname('statuses');
        $entityConfiguration->setUsername('username');
        $entityConfiguration->setPassword('password');
    }
}

//$status = Status::execute(Client::POST, 'update', array('status' => 'testing this out'));

//$status = Status::find('show', '11660732536');

//$status = Status::execute(Client::DELETE, 'destroy/11660732536');