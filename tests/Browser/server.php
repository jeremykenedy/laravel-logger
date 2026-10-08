<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\Tests\TestCase;

$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/fixture-tailwind.css') {
    header('Content-Type: text/css');
    readfile(__DIR__.'/tailwind.css');

    return;
}
if (strpos($path, '/vendor/laravel-logger/') === 0) {
    $file = $root.'/src/resources/assets/'.basename($path);
    if (is_file($file) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['css', 'js'], true)) {
        header('Content-Type: '.(substr($file, -4) === '.css' ? 'text/css' : 'application/javascript'));
        readfile($file);

        return;
    }
}
require $root.'/vendor/autoload.php';

class BrowserFixture extends TestCase
{
    public function bootFixture(): void
    {
        $this->setUp();
        $user = $this->createUser(['name' => 'Morgan Ellis', 'email' => 'morgan@example.com']);
        $this->actingAs($user);
        config(['LaravelLogger.loggerMiddlewareEnabled' => false, 'LaravelLogger.loggerPaginationPerPage' => 5]);
        $css = $_GET['css'] ?? $_COOKIE['logger-browser-css'] ?? 'bootstrap5';
        if (! in_array($css, ['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind'], true)) {
            $css = 'bootstrap5';
        }
        setcookie('logger-browser-css', $css, ['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        config(['LaravelLogger.cssFramework' => $css, 'LaravelLogger.viewStyle' => in_array($css, ['bootstrap3', 'bootstrap4']) ? 'legacy' : 'modern']);
        config(['LaravelLogger.bootstapVersion' => $css === 'bootstrap3' ? '3' : '4']);
        config(['LaravelLogger.theme' => $_GET['theme'] ?? 'system']);
        config(['LaravelLogger.enableSearch' => true]);
        foreach (['Updated billing address', 'Viewed account history', 'Signed in', 'Exported weekly report', 'Changed notification settings', 'Viewed dashboard', 'Updated account profile'] as $index => $description) {
            $this->createActivity(['description' => $description, 'userId' => $user->id, 'created_at' => now()->subHours($index * 5), 'methodType' => $index % 2 ? 'GET' : 'POST', 'route' => 'http://localhost/account/'.($index + 1)]);
        }
        $cleared = $this->createActivity(['description' => 'Cleared account visit', 'userId' => $user->id]);
        $cleared->delete();
    }

    public function application()
    {
        return $this->app;
    }
}

$fixture = new BrowserFixture('browserFixture');
$fixture->bootFixture();
$app = $fixture->application();
$kernel = $app->make(Kernel::class);
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
