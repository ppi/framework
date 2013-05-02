<?php

namespace PPI\DS;

class ConnectionManager
{
    protected $connections;
    protected $libraryToConnMap;

    public function __construct($connections, $libraryToConnMap)
    {
        $this->libraryToConnMap = $libraryToConnMap;
        $this->connections      = $connections;
    }

    public function getConnection($name)
    {
        if(!isset($this->connections[$name])) {
            throw new \Exception('Unable to locate connection by name: ' . $name);
        }

        $library = $this->connections[$name]['library'];
        var_dump($library, $this->libraryToConnMap); exit;
        $conn    = $this->libraryToConnMap[$library]->getConnectionByName($name);

        return $conn;
    }

}

