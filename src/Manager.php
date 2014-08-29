<?php
/**
 * Milkyway Multimedia
 * Manager.php
 *
 * @package relatewell.org.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\Events;

class Manager {
    /** @var array These are the registered listeners */
    protected $listeners = [];

    /** @var array These are the registered listeners that will be only called once and then removed (callbacks if you will) */
    protected $callbacks = [];

    /**
     * Add a listener to a event hook(s)
     *
     * @param string $namespace
     * @param array|string $hooks
     * @param Callable $item
     * @param bool $once Only call the event once (act like a callback)
     */
    public function listen($namespace, $hooks, $item, $once = false) {
        $hooks = (array) $hooks;

        $this->findOrMakeNamespace($namespace);

        foreach($hooks as $hook) {
            if($once) {
                if(!isset($this->callbacks[$namespace][$hook]))
                    $this->callbacks[$namespace][$hook] = [];
            }
            elseif(!isset($this->listeners[$namespace][$hook]))
                $this->listeners[$namespace][$hook] = [];

            // if the attachment is not callable, it will assume that the $hook is a method on the class
            if(!is_callable($item))
                $listener = [$item, $hook];
            else
                $listener = $item;

            if($once) {
                $this->callbacks[$hook][] = $listener;
            }
            else {
                $this->listeners[$hook][] = $listener;
            }
        }
    }

    /**
     * Fire an event(s)
     *
     * @param string $namespace
     * @param array|string $hooks
     */
    public function fire($namespace, $hooks) {
        $hooks = (array)$hooks;

        $this->findOrMakeNamespace($namespace);

        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        foreach($hooks as $hook) {
            if(isset($this->listeners[$namespace][$hook])) {
                foreach($this->listeners[$namespace][$hook] as $listener)
                    call_user_func_array($listener, $args);
            }

            if(isset($this->callbacks[$namespace][$hook])) {
                foreach($this->callbacks[$namespace][$hook] as $listener)
                    call_user_func_array($listener, $args);

                $this->callbacks[$namespace][$hook] = [];
            }
        }
    }

    /**
     * Make namespace if it doesn't exist
     *
     * @param $namespace
     */
    protected function findOrMakeNamespace($namespace) {
        if(!isset($this->listeners[$namespace]))
            $this->listeners[$namespace] = [];

        if(!isset($this->callbacks[$namespace]))
            $this->callbacks[$namespace] = [];
    }
} 