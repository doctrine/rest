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

/**
 * Class that represents a REST server response.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       2.0
 * @version     $Revision$
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Response
{
    private $_requestHandler;
    private $_request;
    private $_responseData;

    public function __construct(Request $request)
    {
        $this->_request = $request;
    }

    public function setRequestHandler(RequestHandler $requestHandler)
    {
        $this->_requestHandler = $requestHandler;
    }

    public function setError($error)
    {
        $this->_responseData = array();
        $this->_responseData['error'] = $error;
    }

    public function setResponseData($responseData)
    {
        $this->_responseData = $responseData;
    }

    public function send()
    {
        $this->_sendHeaders();
        echo $this->getContent();
    }

    public function getContent()
    {
        $data = $this->_responseData;

        switch ($this->_request['_format']) {
            case 'php':
                return serialize($data);
            break;

            case 'json':
                return json_encode($data);
            break;

            case 'xml':
            default:
                return $this->_arrayToXml($data, $this->_request['_entity']);
        }
    }

    private function _sendHeaders()
    {
        if ($this->_requestHandler->getUsername()) {
            if ( ! isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="Doctrine REST API"');
                header('HTTP/1.0 401 Unauthorized');
            } else {
                if ( ! $this->_requestHandler->hasValidCredentials()) {
                    $this->setError('Invalid credentials specified.');
                }
            }
        }

        switch ($this->_request['_format']) {
            case 'php':
                header('Content-type: text/html;');
            break;

            case 'json':
                header('Content-type: text/json;');
                header('Content-Disposition: attachment; filename="' . $this->_request['_action'] . '.json"');
            break;

            case 'xml':
            default:
                header('Content-type: application/xml;');
        }
    }

    private function _arrayToXml($array, $rootNodeName = 'doctrine', $xml = null, $charset = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXmlElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName/>");
        }

        foreach($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $rootNodeName . $key;
            }
            $key = preg_replace('/[^A-Za-z_]/i', '', $key);

            if (is_array($value) && ! empty($value)) {
                $node = $xml->addChild($key);
                $this->_arrayToXml($value, $rootNodeName, $node, $charset);
            } else if ($value) {
                $charset = $charset ? $charset : 'utf-8';
                if (strcasecmp($charset, 'utf-8') !== 0 && strcasecmp($charset, 'utf8') !== 0) {
                    $value = iconv($charset, 'UTF-8', $value);
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                $xml->addChild($key, $value);
            }
        }

        return $this->_formatXml($xml);
    }

    private function _formatXml($simpleXml)
    {
        $xml = $simpleXml->asXml();

        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now indent the tags
        $token = strtok($xml, "\n");
        $result = ''; // holds formatted version as it is built
        $pad = 0; // initial indent
        $matches = array(); // returns from preg_matches()

        // test for the various tag states
        while ($token !== false) {
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) {
                $indent = 0;
            // 2. closing tag - outdent now
            } else if (preg_match('/^<\/\w/', $token, $matches)) {
                $pad = $pad - 4;
            // 3. opening tag - don't pad this one, only subsequent tags
            } elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
                $indent = 4;
            // 4. no indentation needed
            } else {
                $indent = 0; 
            }

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines    
        }
        return $result;
    }
}