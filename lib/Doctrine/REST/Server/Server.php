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
    Doctrine\ORM\Connection;

/**
 * Simple REST server facade.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Server
{
    private $_requestHandler;
    private $_request;
    private $_response;

    public function __construct($source, array $requestData = array())
    {
        $this->_request = new Request($requestData);
        $this->_response = new Response($this->_request);
        $this->_requestHandler = new RequestHandler($source, $this->_request, $this->_response);
    }

    public function execute()
    {
        $this->_requestHandler->execute();
        return $this->_requestHandler->getResponse()->getContent();
    }

    public function setEntityIdentifierKey($entity, $identifierKey)
    {
        $this->_requestHandler->setEntityIdentifierKey($entity, $identifierKey);
    }

    public function setEntityAlias($entity, $alias)
    {
        $this->_requestHandler->setEntityAlias($entity, $alias);
    }

    public function registerAction($action, $className)
    {
        $this->_requestHandler->registerAction($action, $className);
    }

    public function addEntityAction($entity, $action, $className)
    {
        $this->_requestHandler->addEntityAction($entity, $action, $className);
    }

    public function setUsername($username)
    {
        $this->_requestHandler->setUsername($username);
    }

    public function setPassword($password)
    {
        $this->_requestHandler->setPassword($password);
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getRequestHandler()
    {
        return $this->_requestHandler;
    }
}