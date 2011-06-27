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

namespace Doctrine\REST\Client\ResponseTransformer;

/**
 * Standard REST request response handler. Converts a standard REST service response
 * to an array for easy manipulation. Works for both xml and json response types.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class StandardResponseTransformer extends AbstractResponseTransformer
{
    public function transform($data)
    {
        switch ($this->_entityConfiguration->getResponseType()) {
            case 'xml':
                return $this->xmlToArray($data);
            case 'json':
                return $this->jsonToArray($data);
            break;
        }
    }

    public function xmlToArray($object, &$array = array())
    {
        if (is_string($object)) {
            $object = new \SimpleXMLElement($object);
        }
        $children = $object->children();
        $executed = false;
        foreach ($children as $elementName => $node) {
            if (isset($array[$elementName]) && $array[$elementName] !== null) {
                if (isset($array[$elementName][0]) && $array[$elementName][0] !== null) {
                    $i = count($array[$elementName]);
                    $this->xmlToArray($node, $array[$elementName][$i]);
                } else {
                    $tmp = $array[$elementName];
                    $array[$elementName] = array();
                    $array[$elementName][0] = $tmp;
                    $i = count($array[$elementName]);
                    $this->xmlToArray($node, $array[$elementName][$i]);
                }
            } else {
                $array[$elementName] = array();
                $this->xmlToArray($node, $array[$elementName]);
            }
            $executed = true;
        }
        if ( ! $executed && ! $children->getName()) {
            $array = (string) $object;
        }
        return $array;
    }

    public function jsonToArray($json)
    {
        return (array) json_decode($json);
    }
}
