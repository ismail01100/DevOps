<?php
class DatabaseConfig {
    private static $config = [
        'host' => 'db',
        'dbname' => 'db',
        'user' => 'user',
        'password' => 'password'
    ];

    public static function getConfig() {
        return [
            'host' => getenv('DB_HOST') ?: self::$config['host'],
            'dbname' => getenv('DB_NAME') ?: self::$config['dbname'],
            'user' => getenv('DB_USER') ?: self::$config['user'],
            'password' => getenv('DB_PASSWORD') ?: self::$config['password']
        ];
    }
}
?>