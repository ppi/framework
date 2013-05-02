<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;
use Illuminate\Database\Capsule;

class Laravel implements ConnectionInferface
{

    protected $capsule;

    public function __construct(array $connections)
    {

        $useEloquent     = true;
        $fetchMode       = null;
        $defaultConnName = null;

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
        }

        // Setup the capsule
        $capsule = new Capsule(
            $this->prepareCapsuleConfig($connections, $fetchMode, $defaultConnName)
        );

        // If the users are using eloquent, lets boot it
        if($useEloquent) {
            $capsule->bootEloquent();
        }

        $this->capsule = $capsule;

    }

    public function prepareCapsuleConfig($connections, $fetchMode, $defaultConnName)
    {
        return array(
            'connections' => $connections,
            'fetch'       => $fetchMode,
            'default'     => $defaultConnName
        );
    }

    public function getConnectionByName($name)
    {
        return $this->capsule->connection($name);
    }

    public function supports($library)
    {
        return $library === 'laravel';
    }

}

