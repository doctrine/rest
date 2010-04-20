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

use Doctrine\REST\Client\URLGenerator\StandardURLGenerator,
    Doctrine\REST\Client\ResponseTransformer\StandardResponseTransformer;

/**
 * Entity configuration class holds all the configuration information for a PHP5
 * object entity that maps to a REST service.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class EntityConfiguration
{
    private $_prototype;
    private $_reflection;
    private $_reflectionProperties = array();

    private $_attributes = array(
        'class' => null,
        'url' => null,
        'name' => null,
        'username' => null,
        'password' => null,
        'identifierKey' => 'id',
        'responseType' => 'xml',
        'urlGeneratorImpl' => null,
        'responseTransformerImpl' => null,
    );

    public function __construct($class)
    {
        $this->_attributes['class'] = $class;
        $this->_attributes['urlGeneratorImpl'] = new StandardURLGenerator($this);
        $this->_attributes['responseTransformerImpl'] = new StandardResponseTransformer($this);

        $this->_reflection = new \ReflectionClass($class);
        foreach ($this->_reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() == $class) {
                $property->setAccessible(true);
                $this->_reflectionProperties[$property->getName()] = $property;
                $this->_properties[] = $property->getName();
            }
        }
    }

    public function getReflection()
    {
        return $this->_reflection;
    }

    public function getReflectionProperties()
    {
        return $this->_reflectionProperties;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    public function setValue($entity, $field, $value)
    {
        if (isset($this->_reflectionProperties[$field])) {
            $this->_reflectionProperties[$field]->setValue($entity, $value);
        } else {
            $entity->$field = $value;
        }
    }

    public function getValue($entity, $field)
    {
        return $this->_reflectionProperties[$field]->getValue($entity);
    }

    public function generateUrl(array $options)
    {
        return $this->_attributes['urlGeneratorImpl']->generate($options);
    }

    public function setUrl($url)
    {
        $this->_attributes['url'] = rtrim($url, '/');
    }

    public function getUrl()
    {
        return $this->_attributes['url'];
    }

    public function setClass($class)
    {
        $this->_attributes['class'] = $class;
    }

    public function getClass()
    {
        return $this->_attributes['class'];
    }

    public function setName($name)
    {
        $this->_attributes['name'] = $name;
    }

    public function getName()
    {
        return $this->_attributes['name'];
    }

    public function setUsername($username)
    {
        $this->_attributes['username'] = $username;
    }

    public function getUsername()
    {
        return $this->_attributes['username'];
    }

    public function setPassword($password)
    {
        $this->_attributes['password'] = $password;
    }

    public function getPassword()
    {
        return $this->_attributes['password'];
    }

    public function setIdentifierKey($identifierKey)
    {
        $this->_attributes['identifierKey'] = $identifierKey;
    }

    public function getIdentifierKey()
    {
        return $this->_attributes['identifierKey'];
    }

    public function setResponseType($responseType)
    {
        $this->_attributes['responseType'] = $responseType;
    }

    public function getResponseType()
    {
        return $this->_attributes['responseType'];
    }

    public function setURLGeneratorImpl($urlGeneratorImpl)
    {
        $this->_attributes['urlGeneratorImpl'] = $urlGeneratorImpl;
    }

    public function getURLGeneratorImpl()
    {
        return $this->_attributes['urlGeneratorImpl'];
    }

    public function setResponseTransformerImpl($responseHandlerImpl)
    {
        $this->_attributes['responseTransformerImpl'] = $responseHandlerImpl;
    }

    public function getResponseTransformerImpl()
    {
        return $this->_attributes['responseTransformerImpl'];
    }

    public function newInstance()
    {
        if ($this->_prototype === null) {
            $this->_prototype = unserialize(sprintf(
                'O:%d:"%s":0:{}',
                strlen($this->_attributes['class']),
                $this->_attributes['class']
            ));
        }
        return clone $this->_prototype;
    }
}