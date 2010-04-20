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

namespace Doctrine\REST\Server;

use Doctrine\ORM\EntityManager,
    Doctrine\DBAL\Connection;

/**
 * Class responsible for transforming a REST server request to a response.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class RequestHandler
{
    private $_source;
    private $_request;
    private $_response;
    private $_username;
    private $_password;
    private $_credentialsCallback;
    private $_entities = array();

    private $_actions = array(
        'delete' => 'Doctrine\\REST\\Server\\Action\\DeleteAction',
        'get' => 'Doctrine\\REST\\Server\\Action\\GetAction',
        'insert' => 'Doctrine\\REST\\Server\\Action\\InsertAction',
        'update' => 'Doctrine\\REST\\Server\\Action\\UpdateAction',
        'list' => 'Doctrine\\REST\\Server\\Action\\ListAction'
    );

    public function __construct($source, Request $request, Response $response)
    {
        $this->_source = $source;
        $this->_request = $request;
        $this->_response = $response;
        $this->_response->setRequestHandler($this);
        $this->_credentialsCallback = array($this, 'checkCredentials');
    }

    public function configureEntity($entity, $configuration)
    {
        $this->_entities[$entity] = $configuration;
    }

    public function setEntityAlias($entity, $alias)
    {
        $this->_entities[$entity]['alias'] = $alias;
    }

    public function addEntityAction($entity, $action, $className)
    {
        $this->_entities[$entity]['actions'][$action] = $className;
    }

    public function setEntityIdentifierKey($entity, $identifierKey)
    {
        $this->_entities[$entity]['identifierKey'] = $identifierKey;
    }

    public function getEntityIdentifierKey($entity)
    {
        return isset($this->_entities[$entity]['identifierKey']) ? $this->_entities[$entity]['identifierKey'] : 'id';
    }

    public function resolveEntityAlias($alias)
    {
        foreach ($this->_entities as $entity => $configuration) {
            if (isset($configuration['alias']) && $configuration['alias'] === $alias) {
                return $entity;
            }
        }
        return $alias;
    }

    public function setCredentialsCallback($callback)
    {
        $this->_credentialsCallback = $callback;
    }

    public function registerAction($action, $className)
    {
        $this->_actions[$action] = $className;
    }

    public function isSecure()
    {
        return ($this->_username && $this->_password) ? true : false;
    }

    public function checkCredentials($username, $password)
    {
        if ( ! $this->isSecure()) {
            return true;
        }

        if ($this->_username == $username && $this->_password == $password) {
            return true;
        } else {
            return false;
        }
    }

    public function hasValidCredentials()
    {
        $args = array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        return call_user_func_array($this->_credentialsCallback, $args);
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function getActions()
    {
        return $this->_actions;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getEntity()
    {
        return $this->resolveEntityAlias($this->_request['_entity']);
    }

    public function execute()
    {
        try {
            $entity = $this->getEntity();
            $actionInstance = $this->getAction($entity, $this->_request['_action']);

            if (method_exists($actionInstance, 'execute')) {
                $result = $actionInstance->execute();
            } else {
                if ($this->_source instanceof EntityManager) {
                    $result = $actionInstance->executeORM();
                } else {
                    $result = $actionInstance->executeDBAL();
                }
            }

            $this->_response->setResponseData(
                $this->_transformResultForResponse($result)
            );
        } catch (\Exception $e) {
            $this->_response->setError($this->_getExceptionErrorMessage($e));
        }
        return $this->_response;
    }

    public function getAction($entity, $actionName)
    {
        if (isset($this->_actions[$actionName])) {
            if ( ! is_object($this->_actions[$actionName])) {
                $actionClassName = $this->_actions[$actionName];
                $this->_actions[$actionName] = new $actionClassName($this);
            }
            return $this->_actions[$actionName];
        }
        if (isset($this->_entities[$entity]['actions'][$actionName])) {
            if ( ! is_object($this->_entities[$entity]['actions'][$actionName])) {
                $actionClassName = $this->_entities[$entity]['actions'][$actionName];
                $this->_entities[$entity]['actions'][$actionName] = new $actionClassName($this);
            }
            return $this->_entities[$entity]['actions'][$actionName];
        }
    }

    private function _getExceptionErrorMessage(\Exception $e)
    {
        $message = $e->getMessage();

        if ($e instanceof \PDOException) {
            $message = preg_replace("/SQLSTATE\[.*\]: (.*)/", "$1", $message);
        }

        return $message;
    }

    private function _transformResultForResponse($result, $array = null)
    {
        if ( ! $array) {
            $array = array();
        }
        if (is_object($result)) {
            $entityName = get_class($result);
            if ($this->_source instanceof EntityManager) {
                $class = $this->_source->getMetadataFactory()->getMetadataFor($entityName);
                foreach ($class->fieldMappings as $fieldMapping) {
                    $array[$fieldMapping['fieldName']] = $class->getReflectionProperty($fieldMapping['fieldName'])->getValue($result);
                }
            } else {
                $vars = get_object_vars($result);
                foreach ($vars as $key => $value) {
                    $array[$key] = $value;
                }
            }
        } else if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_object($value) || is_array($value)) {
                    if (is_object($value)) {
                        $key = $this->_request['_entity'] . $key;
                    }
                    $array[$key] = $this->_transformResultForResponse($value, isset($array[$key]) ? $array[$key] : array());
                } else {
                    $array[$key] = $value;
                }
            }
        } else if (is_string($result)) {
            $array = $result;
        }
        return $array;
    }
}