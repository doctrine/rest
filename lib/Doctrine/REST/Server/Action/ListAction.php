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

/**
 * REST server list action.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class ListAction extends AbstractAction
{
    public function executeORM()
    {
        $entity = $this->_getEntity();
        $qb = $this->_source->createQueryBuilder()
            ->select('a')
            ->from($entity, 'a');

        $data = $this->_gatherData();
        foreach ($data as $key => $value) {
            $qb->andWhere("a.$key = :$key");
            $qb->setParameter($key, $value);
        }

        $query = $qb->getQuery();
        $this->_setQueryFirstAndMax($query);
        $results = $query->execute();

        return $results;
    }

    public function executeDBAL()
    {
        $entity = $this->_getEntity();

        $params = array();
        $query = sprintf('SELECT * FROM %s', $entity);
        if ($data = $this->_gatherData()) {
            $query .= ' WHERE ';
            foreach ($data as $key => $value) {
                $query .= $key . ' = ? AND ';
                $params[] = $value;
            }
            $query = substr($query, 0, strlen($query) - 5);
        }
        $query = $this->_setQueryFirstAndMax($query);
        $results = $this->_source->fetchAll($query, $params);

        return $results;
    }
}