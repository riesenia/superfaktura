<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;
use GuzzleHttp\Client;

/**
 * Base class for Superfaktura objects
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
abstract class ApiObject implements \JsonSerializable, \ArrayAccess
{
    /**
     * Class name
     *
     * @var string
     */
    protected $_name;

    /**
     * Data
     *
     * @var array
     */
    protected $_data;

    /**
     * Entity id
     *
     * @var int
     */
    protected $_id;

    /**
     * Entity synced
     *
     * @var bool
     */
    protected $_synced = false;

    /**
     * API credentials
     *
     * @var array
     */
    protected $_api;

    /**
     * Configure options for options resolver
     *
     * @param Symfony\Component\OptionsResolver\OptionsResolver
     * @return void
     */
    abstract protected function _configureOptions(OptionsResolver $resolver);

    /**
     * Construct entity using provided data
     *
     * @param array
     * @param string api url
     * @param string user email
     * @param string user api token
     * @param bool if options resolver should be used
     */
    public function __construct($data = array(), $url = null, $email = null, $key = null, $resolveOptions = true)
    {
        // set name
        $name = explode('\\', get_class($this));
        $this->_name = end($name);

        // set API credentials
        $this->_api = array(
            'url' => $url,
            'email' => $email,
            'key' => $key
        );

        $this->_data[$this->_name] = $resolveOptions ? $this->_resolveOptions($data) : $data;
    }

    /**
     * Id setter
     *
     * @param int
     * @return Rshop\Synchronization\Superfaktura\ApiObject
     */
    public function setId($id)
    {
        $this->_id = (int) $id;

        return $this;
    }

    /**
     * Id getter
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get contained data of another entity
     *
     * @param string entity name
     * @return array | Rshop\Synchronization\Superfaktura\ApiObject
     */
    public function get($name)
    {
        // fetch data
        $this->_fetch();

        if (!isset($this->_data[$name])) {
            return false;
        }

        // if this is a sequential array
        if (is_array($this->_data[$name]) && !count(array_filter(array_keys($this->_data[$name]), 'is_string'))) {
            foreach ($this->_data[$name] as &$data) {
                $data = $this->_transformToApiObject($data, $name);
            }
        } else {
            $this->_data[$name] = $this->_transformToApiObject($this->_data[$name], $name);
        }

        return $this->_data[$name];
    }

    /**
     * Transform array to ApiObject
     *
     * @param array data
     * @param string name
     * @return mixed transformed data
     */
    public function _transformToApiObject($data, $name)
    {
        $fullName = __NAMESPACE__ . '\\' . $name;

        if (class_exists($fullName) && !$data instanceof $fullName) {
            $data = new $fullName($data, $this->_api['url'], $this->_api['email'], $this->_api['key'], false);
        }

        return $data;
    }

    /**
     * Call API get
     *
     * @param string link
     * @return array response
     */
    protected function _apiGet($link)
    {
        return $this->_apiCall('GET', $link);
    }

    /**
     * Call API post
     *
     * @param string link
     * @param array data
     * @return array response
     */
    protected function _apiPost($link, $data = null)
    {
        return $this->_apiCall('POST', $link, $data);
    }

    /**
     * Call API method
     *
     * @param string method
     * @param string link
     * @param array data
     * @return array response
     */
    protected function _apiCall($method, $link, $data = null)
    {
        $client = new Client();

        $request = $client->createRequest($method, $this->_api['url'] . $link);
        $request->setHeader('Authorization', 'SFAPI email=' . $this->_api['email'] . '&apikey=' . $this->_api['key']);

        if (!is_null($data)) {
            $request->getBody()->setField('data', json_encode($data));
        }

        try {
            $response = $client->send($request);
        } catch (\Exception $e) {
            $exception = new Exception('Superfaktura API call failed', $e->getCode(), $e);
            $exception->setErrors(array('message' => $e->getMessage()));
            throw $exception;
        }

        $response = json_decode($response->getBody(), true);

        // throw exception on error
        if (isset($response['error']) && $response['error'] > 0) {
            $exception = new Exception('Superfaktura API call error', $response['error']);
            $exception->setErrors($response['error_message']);
            throw $exception;
        }

        return $response;
    }

    /**
     * Resolve options
     *
     * @param array data
     * @return array resolved data
     */
    protected function _resolveOptions($data)
    {
        $resolver = new OptionsResolver();

        // define date normalizer
        $resolver->dateNormalizer = function ($options, $value) {
            $time = strtotime($value);

            if (!$time) {
                throw new \DomainException("Not a valid date: " . $value);
            }

            return date('Y-m-d', $time);
        };

        // define float normalizer
        $resolver->floatNormalizer = function ($options, $value) {
            return str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $value));
        };

        // define bool normalizer
        $resolver->boolNormalizer = function ($options, $value) {
            return (bool) $value;
        };

        $this->_configureOptions($resolver);

        return $resolver->resolve($data);
    }

    /**
     * Fetch data from Superfaktura - should be implemented in child class
     *
     * @return bool true if entity was not synced
     */
    protected function _fetch()
    {
        if (!is_null($this->_id) && !$this->_synced) {
            return $this->_synced = true;
        }

        return false;
    }

    /**
     * Handle dynamic method calls
     *
     * @param string method name
     * @param array arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // get<Entity> method
        if (preg_match('/get([A-Z][a-zA-Z0-9]*)/', $method, $matches)) {
            return call_user_func(array($this, 'get'), $matches[1]);
        }

        throw new \BadMethodCallException("Unknown method: " . $method);
    }

    /**
     * json_encode call
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->_data[$this->_name];
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param mixed offset to set
     * @param mixed value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // fetch data
        $this->_fetch();

        if (is_null($offset)) {
            $this->_data[$this->_name][] = $value;
        } else {
            $this->_data[$this->_name][$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param mixed offset to check for
     * @return bool
     */
    public function offsetExists($offset)
    {
        // fetch data
        $this->_fetch();

        return isset($this->_data[$this->_name][$offset]);
    }

    /**
     * Unset an offset
     *
     * @param mixed offset to unset
     * @return void
     */
    public function offsetUnset($offset)
    {
        // fetch data
        $this->_fetch();

        if ($this->offsetExists($offset)) {
            unset($this->_data[$this->_name][$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param mixed offset to retrieve
     * @return mixed
     */
    public function offsetGet($offset)
    {
        // fetch data
        $this->_fetch();

        return $this->offsetExists($offset) ? $this->_data[$this->_name][$offset] : null;
    }
}
