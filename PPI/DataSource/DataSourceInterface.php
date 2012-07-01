<?php

namespace PPI\DataSource;

interface DataSourceInterface
{
    public function factory(array $options);

    public static function create(array $options = array());

    public function getConnection($key);

    public function getConnectionConfig($key);

}
