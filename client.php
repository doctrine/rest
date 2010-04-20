<?php

require '/Users/jwage/Sites/doctrine2git/lib/Doctrine/Common/ClassLoader.php';

use Doctrine\REST\Client\Client,
    Doctrine\REST\Client\EntityConfiguration,
    Doctrine\REST\Client\Manager,
    Doctrine\REST\Client\Entity,
    Doctrine\Common\ClassLoader;

$classLoader = new ClassLoader('Doctrine\REST', __DIR__ . '/lib');
$classLoader->register();

$client = new Client();

$manager = new Manager($client);
$manager->registerEntity('User');

Entity::setManager($manager);

class User extends Entity
{
    public $id;
    public $username;
    public $password;

    public static function configure(EntityConfiguration $entityConfiguration)
    {
        $entityConfiguration->setUrl('http://localhost/rest/server.php');
        $entityConfiguration->setName('user');
    }
}

$user = User::find(9);
$user->username = 'teetertertsting';
$user->password = 'w00t';
$user->save();
print_r($user);