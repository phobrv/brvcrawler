<?php

namespace Phobrv\BrvCrawler\Facades;

use Illuminate\Support\Facades\Facade;

class BrvCrawler extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'brvcrawler';
    }
}
