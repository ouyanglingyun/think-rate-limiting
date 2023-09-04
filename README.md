# think-RateLimiter

基于 ThinkPHP 缓存实现的限流器，提供了一个简单的方法来限制指定时间内的任何操作。

- [安装](#installation)
- [缓存配置](#config)
- [基础用法](#basicUsage)
    - [手动配置尝试次数](#manuallyIncrementingAttempts)
    - [确定限流器可用性](#determineTheAvailabilityOfCurrentLimiters)
    - [清除尝试次数](#clearingAttempts)

<a name="installation"></a>

## 安装
---
通过 Composer 包管理器安装 **think-think-rate-limiting** :

```
composer require lingyun/think-rate-limiting
```

<a name="config"></a>

## 缓存配置
---
通常情况下，限流器使用你默认的缓存驱动，由 `cache` 配置文件中的 `default` 键定义。你也可以通过在你的应用程序的 `cache` 配置文件中定义一个 `limiter` 来指定限流器应该使用哪一个缓存来驱动：

```
return [
    'default'    =>    'file',
    'stores'    =>    [
        // 文件缓存
        'file'   =>  [
            // 驱动方式
            'type'   => 'file',
            // 设置不同的缓存保存目录
            'path'   => '../runtime/file/',
        ],  
        // redis缓存
        'redis'   =>  [
            // 驱动方式
            'type'   => 'redis',
            // 服务器地址
            'host'       => '127.0.0.1',
        ],  
    ],
    'limiter' => 'redis',
];
```
<a name="basicUsage"></a>

## 基础用法
---
可以通过 `think\facade\RateLimiter` 来操作限流器。限流器提供的最简单的方法是 `attempt` 方法，它将一个给定的回调函数执行次数限制在一个给定的秒数内。

当回调函数执行次数超过限制时， `attempt` 方法返回 `false` ；否则 `attempt` 方法将返回回调的结果或 `true` 。 `attempt` 方法接受的第一个参数是一个速率限制器 `key` ，它可以是你选择的任何字符串，代表被限制速率的动作：

```
use think\facade\RateLimiter;

$executed = RateLimiter::attempt(
    'send-message:'.$user->id,
    $perMinute = 5,
    function() {
        // 发送消息...
    }
);

if (! $executed) {
    return 'Too many messages sent!';
}
```

<a name="manuallyIncrementingAttempts"></a>

### 手动配置尝试次数
如果想手动与限流器交互，可以使用多种方法。例如，可以调用 `tooManyAttempts` 方法来确定给定的限流器是否超过了每分钟允许的最大尝试次数

```
use think\facade\RateLimiter;

if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
    return 'Too many attempts!';
}
```
或者，可以使用 `remaining` 方法检索给定密钥的剩余尝试次数。如果给定的密钥还有重试次数，可以调用 `hit` 方法来增加总尝试次数:

```
use think\facade\RateLimiter;

if (RateLimiter::remaining('send-message:'.$user->id, $perMinute = 5)) {
    RateLimiter::hit('send-message:'.$user->id);

    // 发送消息。。。
}
```

<a name="determineTheAvailabilityOfCurrentLimiters"></a>

### 确定限流器可用性

当一个键没有更多的尝试次数时，`availableIn` 方法返回在更多尝试可用之前剩余的秒数：

```
use think\facade\RateLimiter;

if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
    $seconds = RateLimiter::availableIn('send-message:'.$user->id);

    return 'You may try again in '.$seconds.' seconds.';
}
```

<a name="clearingAttempts"></a>

### 清除尝试次数

可以使用 `clear` 方法重置给定速率限制键的尝试次数。例如，当接收者读取给定消息时，可以重置尝试次数:
```
use app\model\Message;
use think\facade\RateLimiter;

/**
 * 将邮件标记为已读。
 *
 * @param  \app\model\Message  $message
 * @return \app\model\Message
 */
public function read(Message $message)
{
    $message->markAsRead();

    RateLimiter::clear('send-message:'.$message->user_id);

    return $message;
}
```
