<?php

namespace Doctrine\REST\Client\URLGenerator;

/**
 * Apontador REST request URL generator
 *
 * @author      Alexandre Eher <alexandre@eher.com.br>
 */
class ApontadorURLGenerator extends AbstractURLGenerator
{
    public function generate(array $options)
    {
        $id = isset($options['id']) ? $options['id'] : null;
        $action = isset($options['action']) ? $options['action'] : null;
        $parameters = isset($options['parameters']) ? $options['parameters'] : array();

        $parameters['type'] = $this->_entityConfiguration->getResponseType();
        if ($id)
        {
            if ($action !== null)
            {
                $path = sprintf('/%s/%s', $id, $action);
            } else {
                $path = sprintf('/%s', $id);
            }
        } else {
            if ($action !== null)
            {
                $path = sprintf('/%s', $action);
            } else {
                $path = '';
            }
        }
        $url = $this->_entityConfiguration->getUrl() . '/' . $this->_entityConfiguration->getName() . $path;
        if (is_array($parameters) && $parameters) {
            foreach ($this->_entityConfiguration->getProperties() as $field) {
                unset($parameters[$field]);
            }
            if ($parameters) {
                $url .= '?' . http_build_query($parameters);
            }
        }
        return $url;
    }
}