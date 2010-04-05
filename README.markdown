# Doctrine 2 REST Server and Client

The Doctrine 2 REST server and client component is both an easy way to spin up 
REST services for your Doctrine 2 entities as well as a way to work with REST 
services via an ActiveRecord style implementation similiar to ActiveResource in
Ruby on Rails!

## Introduction

The basic concept is simple, you have a REST service (http://api.people.com/person)
and you want to interact with it through a simple ActiveRecord style interface.

First we can retrieve a person:

    $person = Person::find(1); // GET http://api.people.com/person/1.xml

Now we can change some properties of that person:

    $person->setName('Jonathan H. Wage');

Once we're done we can simply save it and the appropriate REST call will be made:

    $person->save(); // PUT http://api.people.com/person/1.xml (name=Jonathan H. Wage)

## Client

The REST client is an ActiveRecord style implementation for working with REST 
services. All you need to do is define some PHP classes that are mapped to some
REST service on the web. Here is an example where we map a Person to 
http://api.people.com/person:

    <?php

    namespace Entities;

    use Doctrine\REST\Client\Entity;

    class Person extends Entity
    {
        private $id;
        private $name;

        public static function configure(EntityConfiguration $entityConfiguration)
        {
            $entityConfiguration->setUrl('http://api.people.com');
            $entityConfiguration->setName('person');
        }

        public function getId()
        {
            return $this->id;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }
    }

Now when we perform some actions it will generate the appropriate REST request,
execute it, transform the response and hydrate the results to your PHP objects.

    $person = new Person();
    $person->setName('Jonathan H. Wage');
    $person->save(); // POST http://api.people.com/person.xml (name=Jonathan H. Wage)

We can retrieve that person again now:

    $person = Person::find($person->getId()); // GET http://api.people.com/person/1.xml

Or you can retrieve all Person objects:

    $persons = Person::findAll();

## Server

The Doctrine 2 REST server allows you to easily expose your entities through some
REST services. This is the raw low level server and does not include any routing
or URL parsing so you would need to implement in some existing framework that
has routing like Symfony or Zend Framework.

All you need to do is create a new REST server instance and pass it the instance
of your EntityManager you want to expose the entities for and an array representing
the server request you want to process:

    $request = array(
        '_method' => 'get',
        '_format' => 'xml',
        '_entity' => 'user',
        '_action' => 'get',
        '_id' => 1
    );

    $server = new \Doctrine\REST\Server\Server($em, $request);
    $server->addEntityAlias('Entities\User', 'user');

    $xml = $server->execute();

The above would retrieve the User with the id of 1 and return an XML document
like the following:

    <user>
        <id>1</id>
        <username>jwage</username>
    </user>