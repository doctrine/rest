<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
*/

namespace Doctrine\REST\Client;

/**
 * Class responsible for managing the entities registered for REST services.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Manager
{
    private $_client;
    private $_entityConfigurations = array();
    private $_identityMap = array();

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    public function registerEntity($entity)
    {
        $this->_entityConfigurations[$entity] = $entity;
    }

    public function getEntityConfiguration($entity)
    {
        if ( ! isset($this->_entityConfigurations[$entity])) {
            throw new \InvalidArgumentException(
                sprintf('Could not find entity configuration for "%s"', $entity)
            );
        }
        if (is_string($this->_entityConfigurations[$entity])) {
            $entityConfiguration = new EntityConfiguration($entity);
            call_user_func_array(
                array($entity, 'configure'),
                array($entityConfiguration)
            );
            $this->_entityConfigurations[$entity] = $entityConfiguration;
        }
        return $this->_entityConfigurations[$entity];
    }

    public function entityExists($entity)
    {
        return $this->getEntityIdentifier($entity) ? true : false;
    }

    public function getEntityIdentifier($entity)
    {
        $configuration = $this->getEntityConfiguration(get_class($entity));
        $identifierKey = $configuration->getIdentifierKey();
        return $configuration->getValue($entity, $identifierKey);
    }

    public function execute($entity, $url = null, $method = Client::GET, $parameters = null)
    {
        if (is_object($entity)) {
            $className = get_class($entity);
        } else {
            $className = $entity;
        }
        $configuration = $this->getEntityConfiguration($className);

        $request = new Request();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setParameters($parameters);
        $request->setUsername($configuration->getUsername());
        $request->setPassword($configuration->getPassword());
        $request->setResponseType($configuration->getResponseType());
        $request->setResponseTransformerImpl($configuration->getResponseTransformerImpl());

        $result =  $this->_client->execute($request);

        if (is_array($result))
        {
            $name = $configuration->getName();

            $identifierKey = $configuration->getIdentifierKey();
            $className = $configuration->getClass();
            if (isset($result[$name]) && is_array($result[$name]))
            {
                $collection = array();
                foreach ($result[$name] as $data) {
                    $identifier = $data[$identifierKey];
                    if (isset($this->_identityMap[$className][$identifier]))
                    {
                        $instance = $this->_identityMap[$className][$identifier];
                    } else {
                        $instance = $configuration->newInstance();
                        $this->_identityMap[$className][$identifier] = $instance;
                    }
                    $collection[] = $this->_hydrate(
                        $configuration, $instance, $data
                    );
                }
                return $collection;
            } else if ($result) {
                
                if (is_object($entity))
                {
                    $instance = $entity;
                    $this->_hydrate($configuration, $instance, $result);
                    $identifier = $this->getEntityIdentifier($instance);
                    $this->_identityMap[$className][$identifier] = $instance;
                } else {
                    $identifier = $result[$identifierKey];
                    if (isset($this->_identityMap[$className][$identifier]))
                    {
                        $instance = $this->_identityMap[$className][$identifier];
                    } else {
                        $instance = $configuration->newInstance();
                        $this->_identityMap[$className][$identifier] = $instance;
                    }
                    $this->_hydrate($configuration, $instance, $result);
                }
                return $instance;
            }
        } else {
            return array();
        }
    }

    private function _hydrate($configuration, $instance, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value))
            {
                $configuration->setValue($instance, $key, (string) $value);
            } else {
                $configuration->setValue($instance, $key, $value);
            }
        }

        return $instance;
    }
}