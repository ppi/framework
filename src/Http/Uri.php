<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Http;

use Phly\Http\Uri as PhlyHttpUri;
use Psr\Http\Message\UriInterface;

/**
 * Class Uri. PHP-5.3 backport of Phly\Http\Uri.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class Uri extends PhlyHttpUri implements UriInterface
{
    /**
     * @var int[] Array indexed by valid scheme names to their corresponding ports.
     */
    protected $allowedSchemes = array(
        'http'  => 80,
        'https' => 443,
    );
}
