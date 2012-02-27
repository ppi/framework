<?php
namespace PPI\Module;
use Symfony\Component\Routing\Loader\YamlFileLoader,
	Symfony\Component\Config\FileLocator;

class Module {
	
	function loadYamlRoutes($path) {
		$loader = new YamlFileLoader(new FileLocator(array(dirname($path))));
		$routesCollection = $loader->load(pathinfo($path, PATHINFO_FILENAME) . '.' . pathinfo($path, PATHINFO_EXTENSION));
		return $routesCollection;
	}
	
}