<?php
namespace PPI\DS;

interface ConnectionInferface
{
    public function getConnectionByName($name);

    public function supports($library);
}
