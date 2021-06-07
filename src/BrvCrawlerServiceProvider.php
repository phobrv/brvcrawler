<?php

namespace Phobrv\BrvCrawler;

use Illuminate\Support\ServiceProvider;

class BrvCrawlerServiceProvider extends ServiceProvider {
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot(): void{
		$this->migrations();
		view()->composer(
			'phobrv::*',
			'Phobrv\BrvCrawler\ViewComposers\CrawComposer'
		);
		$this->loadRepositories();
		// $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'phobrv');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'phobrv');
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		$this->loadRoutesFrom(__DIR__ . '/routes.php');

		// Publishing is only necessary when using the CLI.
		if ($this->app->runningInConsole()) {
			$this->bootForConsole();
		}
	}

	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register(): void{
		$this->app->singleton(\Phobrv\BrvCrawler\ViewComposers\CrawComposer::class);
		$this->mergeConfigFrom(__DIR__ . '/../config/brvcrawler.php', 'brvcrawler');

		// Register the service the package provides.
		$this->app->singleton('brvcrawler', function ($app) {
			return new BrvCrawler;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return ['brvcrawler'];
	}

	/**
	 * Console-specific booting.
	 *
	 * @return void
	 */
	protected function bootForConsole(): void {
		// Publishing the configuration file.
		// $this->publishes([
		// 	__DIR__ . '/../config/brvcrawler.php' => config_path('brvcrawler.php'),
		// ], 'brvcrawler.config');

		// Publishing the views.
		/*$this->publishes([
	            __DIR__.'/../resources/views' => base_path('resources/views/vendor/phobrv'),
*/

		// Publishing assets.
		/*$this->publishes([
	            __DIR__.'/../resources/assets' => public_path('vendor/phobrv'),
*/

		// Publishing the translation files.
		/*$this->publishes([
	            __DIR__.'/../resources/lang' => resource_path('lang/vendor/phobrv'),
*/

		// Registering package commands.
		// $this->commands([]);
	}
	public function migrations() {
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		// $this->publishes([
		//  __DIR__ . '/../database/Migrations/' => database_path('migrations'),
		// ], 'brvcore.migrations');
	}
	protected function loadRepositories() {
		$this->app->bind(
			\Phobrv\BrvCrawler\Repositories\CrawlerDataRepository::class,
			\Phobrv\BrvCrawler\Repositories\CrawlerDataRepositoryEloquent::class
		);
		$this->app->bind(
			\Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository::class,
			\Phobrv\BrvCrawler\Repositories\CrawlerProfileRepositoryEloquent::class
		);
	}
}
