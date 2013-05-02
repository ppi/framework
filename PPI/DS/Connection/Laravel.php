<?php

namespace PPI\DS\Connection;

use PPI\DS\ConnectionInferface;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\ConnectionResolver;

class Laravel implements ConnectionInferface
{

    protected $connFactory;
    protected $resolver;

    public function __construct(array $connections)
    {
        $connectionFactory = new ConnectionFactory(new \Illuminate\Container\Container());
        $resolver = new ConnectionResolver();
        foreach($connections as $name => $conn) {
            $resolver->addConnection($name, $connectionFactory->make($conn));
        }
        $resolver->setDefaultConnection('default');

        $this->resolver = $resolver;
        $this->connFactory = $connectionFactory;

    }

    public function getConnectionByName($name)
    {
        if(!$this->resolver->hasConnection($name)) {
             throw new \Exception('No connection found named: ' . $name);
        }
        return $this->resolver->connection($name);
    }

    public function supports($library)
    {
        return $library === 'laravel';
    }

}

