<?php

declare(strict_types=1);

namespace think\facade;

use think\Facade;

class RateLimiter extends Facade
{

    protected static function getFacadeClass()
    {
        return \think\cache\RateLimiter::class;
    }
}
