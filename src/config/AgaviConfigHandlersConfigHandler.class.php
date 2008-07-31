<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2008 the Agavi Project.                                |
// | Based on the Mojavi3 MVC Framework, Copyright (c) 2003-2005 Sean Kerr.    |
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
 * AgaviConfigHandlersConfigHandler allows you to specify configuration handlers
 * for the application or on a module level.
 *
 * @package    agavi
 * @subpackage config
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class AgaviConfigHandlersConfigHandler extends AgaviConfigHandler
{
	/**
	 * Execute this configuration handler.
	 *
	 * @param      string An absolute filesystem path to a configuration file.
	 * @param      string An optional context in which we are currently running.
	 *
	 * @return     string Data to be written to a cache file.
	 *
	 * @throws     <b>AgaviUnreadableException</b> If a requested configuration
	 *                                             file does not exist or is not
	 *                                             readable.
	 * @throws     <b>AgaviParseException</b> If a requested configuration file is
	 *                                        improperly formatted.
	 *
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	public function execute($config, $context = null)
	{
		// parse the config file
		$configurations = $this->orderConfigurations(AgaviConfigCache::parseConfig($config, false, $this->getValidationFile(), $this->parser)->configurations, AgaviConfig::get('core.environment'));
		                                                                                                                                                                         
		// init our data arrays
		$handlers = array();
		
		foreach($configurations as $cfg) {
			if(!isset($cfg->handlers)) {
				continue;
			}
			
			// let's do our fancy work
			foreach($cfg->handlers as $handler) {
				$pattern = $handler->getAttribute('pattern');
				
				$category = AgaviToolkit::normalizePath(AgaviToolkit::expandDirectives($pattern));
				
				$class = $handler->getAttribute('class');
				
				$transformations = array();
				if(isset($handler->transformations)) {
					foreach($handler->transformations as $transformation) {
						$transformationPath = AgaviToolkit::literalize($transformation->getValue());
						$transformations[] = $transformationPath;
					}
				}
				
				$validations = array(
					AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG    => array(
					),
					AgaviXmlConfigParser::VALIDATION_TYPE_SCHEMATRON => array(
					),
					AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA  => array(
					),
				);
				// legacy: via attribute
				if($handler->hasAttribute('validate')) {
					$validations[AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA][] = AgaviToolkit::literalize($handler->getAttribute('validate'));
				}
				if(isset($handler->validations)) {
					foreach($handler->validations as $validation) {
						$validationPath = AgaviToolkit::literalize($validation->getValue());
						if(!$validation->hasAttribute('type')) {
							$validationType = $this->guessValidationType($validationPath);
						} else {
							$validationType = $validation->getAttribute('type');
						}
						$validations[$validationType][] = $validationPath;
					}
				}
				
				$handlers[$category] = array(
					'class' => $class,
					'parameters' => $this->getItemParameters($handler),
					'transformations' => $transformations,
					'validations' => $validations,
				);
			}
		}
		
		$data = array(
			'self::$handlers += ' . var_export($handlers, true),
		);
		
		return $this->generate($data);
	}
	
	public function guessValidationType($path)
	{
		switch(pathinfo($path, PATHINFO_EXTENSION)) {
			case 'rng':
				return AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG;
			case 'rnc':
				return AgaviXmlConfigParser::VALIDATION_TYPE_RELAXNG;
			case 'sch':
				return AgaviXmlConfigParser::VALIDATION_TYPE_SCHEMATRON;
			case 'xsd':
				return AgaviXmlConfigParser::VALIDATION_TYPE_XMLSCHEMA;
			default:
				throw new AgaviException(sprintf('Could not determine validation type for file "%s"', $path));
		}
	}
}

?>