<?
    use Illuminate\Support\Facades\Config;
Config::set('app.commit_sha', env('APP_COMMIT_SHA', ''));
Config::set('app.version', env('APP_VERSION', ''));
