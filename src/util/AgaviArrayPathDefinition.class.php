<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2006 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * AgaviPath implements handling of virtual paths
 * 
 * This class does not implement real filesystem path handling, but uses virtual
 * paths. It is primary used in the validation system for handling arrays of
 * input. 
 *
 * @package    agavi
 * @subpackage util
 *
 * @author     Uwe Mesecke <uwe@mesecke.net>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  (c) Authors
 * @since      0.11.0
 *
 * @version    $Id: AgaviVirtualArrayPath.class.php 782 2006-07-20 23:49:36Z dominik $
 */
final class AgaviArrayPathDefinition
{
	/**
	 * constructor
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	private function __construct()
	{
	}


	public static function &getValueFromArray($parts, &$array, $default = null)
	{
		$a = &$array;

		foreach($parts as $part) {
			if($part) {
				if(isset($a[$part])) {
					$a = &$a[$part];
				} else {
					//throw new AgaviException('The part: ' . $part . ' does not exist in the given array');
					return $default;
				}
			}
		}

		return $a;
	}

	public function setValueFromArray($parts, &$array, $value)
	{
		$a = &$array;

		foreach($parts as $part) {
			if($part) {
				if(!isset($a[$part])) {
					$a[$part] = array();
				}
				$a = &$a[$part];
			}
		}

		$a = $value;
	}

	public function getPartsFromPath($path)
	{
		$parts = array();
		$absolute = ($path[0] != '[');
		if(($pos = strpos($path, '[')) === false) {
			$parts[] = substr($path, 0, strlen($path));
		}
		else {
			if($absolute) {
				$parts[] = substr($path, 0, $pos);
			}

			$parts = array_merge($parts, explode('][', rtrim(ltrim(substr($path, $pos), '['), ']')));
		}

		return array('parts' => $parts, 'absolute' => $absolute);
	}
}
?>