<?php

use think\App;
use think\Service;
use think\cache\RateLimiter;

class RateLimiterService extends Service
{
    public function register()
    {
        $this->app->bind(RateLimiter::class, function (App $app) {
            return new RateLimiter($app->make('cache')->driver(
                $app->config->get('cache.limiter')
            ));
        });
    }
}
