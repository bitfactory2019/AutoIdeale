<?php

declare(strict_types=1);

namespace App;

use Nette\Configurator;


class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;

		$configurator->setDebugMode(false); // enable for your remote IP
		$configurator->enableTracy(__DIR__ . '/../log');

		$configurator->setTimeZone('Europe/Rome');
		$configurator->setTempDirectory(__DIR__ . '/../temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig(__DIR__ . '/config/common.neon');
		$configurator->addConfig(__DIR__ . '/config/local.neon');

		$configurator->addParameters([
			'tempImagesDir' => '/temp/images/',
			'postsImagesDir' => '/images/posts/',
			'usersImagesDir' => '/images/users/',
			'templateEmailsDir' => '%appDir%/Presenters/templates/Emails/',
			'mapboxToken' => 'pk.eyJ1IjoibGFuZG9scyIsImEiOiJjazdsZzA1eGMwNnphM2VtdGl1bjd1ZGppIn0.dsThp_5T7w_YA4zxJcNnZA'
		]);

		return $configurator;
	}
}
