<?php
namespace GGuney\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

class Rbac extends Facade {

	/**
	 * Facade for usage from anywhere in your app.
	 * 
	 * @return string
	 */
	protected static function getFacadeAccessor() {
		return 'GGuney\Rbac\Rbac';
	}
	
}