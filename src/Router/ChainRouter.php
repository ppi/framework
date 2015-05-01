<?php

namespace PPI\Framework\Router;

use Symfony\Cmf\Component\Routing\ChainRouter as BaseChainRouter;

class ChainRouter extends BaseChainRouter
{

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function parametersToString(array $parameters)
    {
        $pieces = array();
        foreach ($parameters as $key => $val) {
            $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
        }

        return implode(', ', $pieces);
    }

}