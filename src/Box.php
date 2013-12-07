<?php
namespace box;

use Closure;

class Box
{

    /**
     * The defined dependency definitions
     *
     * @var array
     */
    protected $_definitions = [];

    /**
     * Defining a factory.
     *
     * @param  string          $id         The name of the definition.
     * @param  string|Closure  $definition A fully namespaced classname or a closure.
     * @throws BoxException if the definition is not a closure or a string.
     */
    public function factory($name, $definition)
    {
        if (!is_string($definition) && !$definition instanceof Closure) {
            throw new BoxException("Error `{$name}` is not a closure definition dependency can't use it as a factory definition.");
        }
        $this->_set($name, $definition, 'factory');
    }

    /**
     * Defining a service (i.e. singleton).
     *
     * @param  string $id         The name of the definition.
     * @param  mixed  $definition The variable to share.
     */
    public function service($name, $definition)
    {
        $this->_set($name, $definition, 'service');
    }

    /**
     * Stores a dependency definition.
     *
     * @param  string $id         The name of the definition.
     * @param  mixed  $definition The definition.
     * @param  mixed  $type       The type of the definition.
     */
    protected function _set($name, $definition, $type)
    {
        if ($definition instanceof Closure) {
            $definition = $definition->bindTo($this, get_class($this));
        }
        $this->_definitions[$name] = compact('definition', 'type');
    }

    /**
     * Checks if a dependency definition exists.
     *
     * @param  string $id The name of the definition.
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->_definitions[$name]);
    }

    /**
     * Gets a shared variable or an new instance.
     *
     * @param  string $name The name of the definition.
     * @return mixed        The shared variable or an new instance.
     * @throws BoxException if the definition doesn't exists.
     */
    public function get($name, $params = [])
    {
        if (!isset($this->_definitions[$name])) {
            throw new BoxException("Unexisting `{$name}` definition dependency.");
        }

        extract($this->_definitions[$name]);

        if ($type === 'singleton') {
            return $definition;
        }

        if ($type === 'service') {
            return $definition = $this->_service($name, $definition);
        }
        return $definition = $this->_factory($definition, $params);
    }

    /**
     * Returns a dependency container.
     *
     * @param  string $name The name of the definition.
     * @return mixed        The shared variable or an new instance.
     * @throws BoxException if the definition doesn't exists.
     */
    public function wrap($name, $params = [], $wrapper = 'box\Wrapper')
    {
        if (!isset($this->_definitions[$name])) {
            throw new BoxException("Unexisting `{$name}` definition dependency.");
        }
        if (!$this->_definitions[$name]['definition'] instanceof Closure) {
            throw new BoxException("Error `{$name}` is not a closure definition dependency can't be wrapped.");
        }
        return new $wrapper([
            'box'    => $this,
            'name'   => $name,
            'params' => $params
        ]);
    }

    /**
     * Process a shared definition.
     *
     * @param  string $name       The name of the definition.
     * @param  mixed  $definition A definition.
     * @return mixed
     */
    protected function _service($name, $definition)
    {
        if ($definition instanceof Closure) {
            $type = 'singleton';
            $definition = $definition();
            $this->_definitions[$name] = compact('definition', 'type');
        }
        return $definition;
    }

    /**
     * Process a setted definition.
     *
     * @param  mixed $definition A definition.
     * @param  array $params     Parameters to pass to the definition.
     * @return mixed
     */
    protected function _factory($definition, $params)
    {
        if (is_string($definition)) {
            return new $definition(isset($params[0]) ? $params[0] : [], $this);
        }
        return call_user_func_array($definition, $params);
    }

    /**
     * Removes a dependency definition.
     *
     * @param  string $name The name of the definition to remove.
     */
    public function remove($name)
    {
        unset($this->_definitions[$name]);
    }

    /**
     * Clears all dependency definitions.
     */
    public function clear()
    {
        $this->_definitions = [];
    }
}
