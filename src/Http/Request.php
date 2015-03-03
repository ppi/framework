<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Http;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Request represents an HTTP request.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class Request extends BaseRequest implements RequestInterface
{
    // ...
}
