<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Erich Beyrent <erich.beyrent@pearson.com>
 * @license  Pearson http://polaris.fen.com/
 * @version  $Revision$
 * @link     $HeadURL$
 *
 */

namespace Mopsy;

/**
 * A simple service container with dependency injection
 */
class Container implements \ArrayAccess
{
    /**
     * Stack of services
     */
    protected $services = array();

    /**
     * Add an object to the container
     *
     * @param string $key The name of a service to set in the container
     * @param mixed $value A closure of object representing a serive
     * @return $this
    */
    public function set($key, $value = null)
    {
        $this->services[$key] = $value;
        return $this;
    }

    /**
     * Get an object from the container
     *
     * @param string @key The name of a service to get from the container
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf('Object %s does not exist.', $key));
        }

        if ($this->services[$key] instanceof \Closure) {
            $this->services[$key] = $this->services[$key]->__invoke();
        }

        return $this->services[$key];
    }

    /**
     * Check to see if a service has been filled into the container
     *
     * @param string @key The name of a service to check in the container
     */
    public function has($key)
    {
        return array_key_exists($key, $this->services);
    }

    /**
     *  Create a new instance of a class
     *
     * This method will populate any services that have been wired into the container
     *
     * @param string $class Class name to instantiate
     * @param array $params An optional array of parameters to be passed into the constructor of a new instance
     * @return Instance of the class sent in as a parameter
     */
    public function newInstance($class, array $params = null, array $ignore = array())
    {
        $reflection = new \ReflectionClass($class);

        $params = !is_null($params) ? $params : $this->identifyConstructorParams($reflection);

        $obj = call_user_func_array(array($reflection, 'newInstance'), $params);

        $this->injectSetters($reflection, $obj, $ignore);
        return $obj;
    }

    /**
     * Identify services from constructor parameters
     *
     * @param \ReflectionClass $reflection An instance of a reflection to inspect
     * @return array Collection of services to be applied as parameters to a constructor
     */
    protected function identifyConstructorParams(\ReflectionClass $reflection)
    {
        $params = array();

        if (!$reflection->hasMethod('__construct') || !$reflection->getMethod('__construct')->isPublic()) {
            return $params;
        }

        foreach ($reflection->getMethod('__construct')->getParameters() as $param) {
            if (array_key_exists($param->getName(), $this->services)) {
                $params[] = $this->get($param->getName());
            } else {
                $params[] = null;
            }
        }

        return $params;
    }

    /**
     * Identify setters elligible for service injection and set them
     *
     * @param \ReflectionClass $reflection A reflection of {@link $obj}
     * @param object $obj The object to call setters on
     * @uses get()
     */
    protected function injectSetters(\ReflectionClass $reflection, $obj, array $ignore = array())
    {
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            $params = $method->getParameters();
            $serviceName = lcfirst(substr($methodName, 3));

            if (substr($methodName, 0, 3) == 'set' &&
                count($params) == 1 &&
                array_key_exists($serviceName, $this->services) &&
                !in_array($serviceName, $ignore)) {

                $obj->$methodName(
                    $this->get($serviceName)
                );
            }
        }
    }

    /**
     * Array access method for setting a service
     *
     * @uses $this->set()
     * @param string $key The name of a service to set in the container
     * @param mixed $value A closure of object representing a serive
     * @return $this
     */
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Array access method for getting a service
     *
     * @uses $this->get()
     * @param string $key The name of a service to get from the container
     * @return mixed The service being retrieved from the container
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Array access method for unsetting a service
     *
     * This is NOT implemented and will throw an exception
     *
     * @param string $key The name of a service to unset in the container
     * @throws \Exception
     */
    public function offsetUnset($key)
    {
        throw new \BadFunctionCallException("Not implemented!");
    }

    /**
     * Array access method for checking is a service is set
     *
     * @param string $key The name of a service to check in the container
     * @return bool True if the service exists, false if it does not
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }
}