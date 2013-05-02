<?php
namespace PPI\DataSource;

interface ConnectionInferface
{
    public function getConnectionByName($name);

    public function supports($library);
}
