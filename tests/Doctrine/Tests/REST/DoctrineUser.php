<?php

namespace Doctrine\Tests\REST;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity()
 * @Table(name="user")
 */
class DoctrineUser
{
    /**
     * @Id() @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string", length=255, unique=true)
     */
    private $username;

    public function setUsername($username)
    {
        $this->username = $username;
    }
}
