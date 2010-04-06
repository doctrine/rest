# Doctrine REST Server

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