<?php

namespace PPI\DataSource;

class ConnectionManager
{
    protected $connections;
    protected $connectionClassMap;
    protected $libraryToConnMap;

    public function __construct($connections, $connectionClassMap)
    {
        $this->connections        = $connections;
        $this->connectionClassMap = $connectionClassMap;
    }

    public function getConnection($name)
    {
        if (!isset($this->connections[$name])) {
            throw new \Exception('Unable to locate connection by name: ' . $name);
        }

        // Who's the vendor?
        $library = $this->connections[$name]['library'];

        // Have we been here before?
        if (!isset($this->libraryToConnMap[$library])) {

            // Identify the vendor connection classname from the classmap
            $connectionClass = $this->connectionClassMap[$library];

            // Create the connection object and keep it for future use
            $this->libraryToConnMap[$library] = new $connectionClass($this->getConnectionsByLibrary($library));

        }

        return $this->libraryToConnMap[$library]->getConnectionByName($name);
    }

    public function getConnectionsByLibrary($library)
    {
        $conns = array();
        foreach ($this->connections as $name => $config) {
            if ($config['library'] === $library) {
                $conns[$name] = $config;
            }
        }

        return $conns;
    }

}
