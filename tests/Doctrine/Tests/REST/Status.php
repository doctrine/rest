<?php

namespace Doctrine\Tests\REST;

use Doctrine\REST\Client\Entity;
use Doctrine\REST\Client\EntityConfiguration;

class Status extends Entity
{
    private $id;
    private $status;
    private $text;

    public static function configure(EntityConfiguration $entityConfiguration)
    {
        $entityConfiguration->setUrl('http://twitter.com');
        $entityConfiguration->setName('statuses');
        $entityConfiguration->setUsername('username');
        $entityConfiguration->setPassword('password');
    }
}
