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

namespace Doctrine\REST\Server\Action;

use \Doctrine\REST\Server\RequestHandler;

/**
 * Abstract server action class for REST server actions to extend from.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class AbstractAction
{
    protected $_requestHandler;
    protected $_em;
    protected $_request;

    public function __construct(RequestHandler $requestHandler)
    {
        $this->_requestHandler = $requestHandler;
        $this->_em = $requestHandler->getEntityManager();
        $this->_request = $requestHandler->getRequest();
    }

    abstract public function execute();

    protected function _resolveEntityAlias($alias)
    {
        return $this->_requestHandler->resolveEntityAlias($alias);
    }

    protected function _setQueryFirstAndMax($q)
    {
        if ( ! isset($this->_request['_page']) && ! isset($this->_request['_first']) && ! isset($this->_request['_max'])) {
            $this->_request['_page'] = '1';
        }
        $maxPerPage = isset($this->_request['_max_per_page']) ? $this->_request['_max_per_page'] : 20;
        if (isset($this->_request['_page'])) {
            $page = $this->_request['_page'];
            $first = ($page - 1) * $maxPerPage;
            $q->setFirstResult($first);
            $q->setMaxResults($maxPerPage);
        } else {
            if (isset($this->_request['_first'])) {
                $q->setFirstResult($this->_request['_first']);
            } else {
                $q->setFirstResult(0);
            }
            if (isset($this->_request['_max'])) {
                $q->setMaxResults($this->_request['_max']);
            } else {
                $q->setMaxResults($maxPerPage);
            }
        }
    }

    protected function _getFindByIdQuery($entity, $id)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from($entity, 'a')
            ->where('a.id = ?1')
            ->setParameter('1', $id);
        $q = $qb->getQuery();
        return $q;
    }

    protected function _gatherData()
    {
        $data = array();
        foreach ($this->_request->getData() as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            $data[$key] = $value;
        }
        return $data;
    }
}