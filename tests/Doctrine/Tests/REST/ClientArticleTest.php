<?php

namespace Doctrine\Tests\REST;

use Doctrine\REST\Client\Entity;
use Doctrine\REST\Client\EntityConfiguration;

class ClientArticleTest extends Entity
{
    private $id;
    private $title;

    public static function configure(EntityConfiguration $entityConfiguration)
    {
        $entityConfiguration->setUrl('http://api.people.com');
        $entityConfiguration->setName('article');
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
