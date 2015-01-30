<?php
namespace Rshop\Synchronization;

/**
 * Factory for Superfaktura objects
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class Superfaktura
{
    /**
     * Superfaktura API URL
     */
    const SFAPI_URL = 'https://moja.superfaktura.sk/';

    /**
     * Email
     *
     * @var string
     */
    protected $_email;

    /**
     * API key
     *
     * @var string
     */
    protected $_apiKey;

    /**
     * Constructor
     *
     * @param string email
     * @param string api key
     */
    public function __construct($email, $apiKey)
    {
        $this->_email = $email;
        $this->_apiKey = $apiKey;
    }

    /**
     * Create and return instance of requested entity
     *
     * @param string entity name
     * @param string optional data
     * @return Rshop\Synchronization\Superfaktura\ApiObject
     */
    public function create($name, $data = array())
    {
        $fullName = __NAMESPACE__ . '\\Superfaktura\\' . $name;

        if (!class_exists($fullName)) {
            throw new \DomainException("Not allowed entity: " . $name);
        }

        return new $fullName($data, self::SFAPI_URL, $this->_email, $this->_apiKey);
    }

    /**
     * Get instance of requested entity with requested id
     *
     * @param string entity name
     * @param int id
     * @return Rshop\Synchronization\Superfaktura\ApiObject
     */
    public function get($name, $id)
    {
        return $this->create($name)->setId($id);
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
        // create<Entity> method
        if (preg_match('/create([A-Z][a-zA-Z0-9]*)/', $method, $matches)) {
            return call_user_func(array($this, 'create'), $matches[1], isset($arguments[0]) ? $arguments[0] : array());
        }

        // get<Entity> method
        if (preg_match('/get([A-Z][a-zA-Z0-9]*)/', $method, $matches)) {
            if (!isset($arguments[0])) {
                throw new \DomainException("Entity ID not set.");
            }

            return call_user_func(array($this, 'get'), $matches[1], $arguments[0]);
        }

        throw new \BadMethodCallException("Unknown method: " . $method);
    }
}
