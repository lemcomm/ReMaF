<?php
/**
 * Copyright (C) 2012 Derek J. Lambert
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace App\ORM\Query\AST\Functions\PostgreSql;

use LongitudeOne\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * ST_Extent DQL function
 *
 * @author  Tom Vogt <tom@lemuria.org>
 * @author  Andrew Gwynn <andrew@iungard.com>
 * @license http://mit-license.org MIT
 */
class SpExtent extends AbstractSpatialDQLFunction
{
	/**
	 * Function SQL name getter.
	 *
	 * @since 2.0 This function replace the protected property functionName.
	 */
	protected function getFunctionName(): string {
		return 'ST_Extent';
	}/**
	 * Maximum number of parameter for the spatial function.
	 *
	 * @return int the inherited methods shall NOT return null, but 0 when function has no parameter
	 * @since 2.0 This function replace the protected property maxGeomExpr.
	 *
	 */
	protected function getMaxParameter(): int {
		return 1;
	}

	/**
	 * Minimum number of parameter for the spatial function.
	 *
	 * @return int the inherited methods shall NOT return null, but 0 when function has no parameter
	 * @since 2.0 This function replace the protected property minGeomExpr.
	 *
	 */
	protected function getMinParameter(): int {
		return 1;
	}

	/**
	 * Get the platforms accepted.
	 *
	 * @return string[] a non-empty array of accepted platforms
	 * @since 2.0 This function replace the protected property platforms.
	 *
	 */
	protected function getPlatforms(): array {
		return ['postgresql'];
	}
}
