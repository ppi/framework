<?php

namespace PPI\DataSource\Connection;

use PDO;
use PPI\DataSource\ConnectionInferface;
use Illuminate\Database\Capsule\Manager as Capsule;

class Laravel implements ConnectionInferface
{

    protected $capsule;

    public function __construct(array $connections)
    {

        $useEloquent     = true;
        $fetchMode       = null;
        $defaultConnName = null;

        $capsule = new Capsule;

        foreach($connections as $name => $conn) {
            if(!$useEloquent && isset($conn['eloquent'])) {
                $useEloquent = true;
            }

            if($fetchMode === null && isset($conn['fetch_mode']) && !empty($conn['fetch_mode'])) {
                $fetchMode = $conn['fetch_mode'];
            }

            if($defaultConnName === null && isset($conn['default']) && $conn['default'] === true) {
                $defaultConnName = $name;
            }

            $capsule->addConnection($this->normaliseConfigKeys($conn), $name);
        }

        // Set the Capsule configuration options
        $config = $capsule->getContainer()->make('config');
        $config['database.fetch'] = $fetchMode ?: PDO::FETCH_ASSOC;
        $config['database.default'] = $defaultConnName ?: 'default';
        $capsule->getContainer()->instance('config', $config);

        // If the users are using eloquent, lets boot it
        if($useEloquent) {
            $capsule->bootEloquent();
        }

        $capsule->setAsGlobal();
        $this->capsule = $capsule;

    }

    public function getConnectionByName($name)
    {
        return $this->capsule->getConnection($name);
    }

    public function normaliseConfigKeys($config)
    {
        $keys = array('hostname' => 'host');
        foreach($keys as $findKey => $replaceKey) {
            if(isset($config[$findKey])) {
                $config[$replaceKey] = $config[$findKey];
                unset($config[$findKey]);
            }
        }
        return $config;
    }

    public function supports($library)
    {
        return $library === 'laravel';
    }

}
