<?php

namespace Doctrine\Tests\REST;

use Doctrine\REST\Client\Entity;
use Doctrine\REST\Client\EntityConfiguration;

class User extends Entity
{
    protected $id;
    protected $username;

    public static function configure(EntityConfiguration $entityConfiguration)
    {
        $entityConfiguration->setUrl('api');
        $entityConfiguration->setName('user');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }
}
