<?php

declare(strict_types=1);

namespace AdvertisingSettings;

class ASLog
{
    public static function createTable(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            log TEXT NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function l( string $text ): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        $wpdb->insert(
            $table_name,
            [
                'log' => $text,
            ]
        );

        if (!defined('WP_CLI')) {
            return;
        }

        $date = current_time('c');
        \WP_CLI::log(
            \WP_CLI::colorize("%W[$date] %n$text")
        );
    }

    /**
     * Log multiple lines at once
     *
     * @param array<string> $lines List of lines to log
     */
    public static function lines( array $lines ): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        $current_time = current_time('mysql');

        $query = "INSERT INTO $table_name (log) VALUES " .
            implode(
                ',',
                array_fill(0, count($lines), '(%s)')
            );

        $wpdb->query($wpdb->prepare($query, $lines));
    }

    /**
     * Get all log lines
     *
     * @return array<mixed> array of Log items
     */
    public static function getAll(): array
    {
        global $wpdb;
        $logs = [];

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        return $wpdb->get_results("SELECT time, log FROM $table_name ORDER BY id DESC");
    }

    /**
     * Poll latest log lines
     */
    public static function poll(): string
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        $logs = $wpdb->get_col(
            "SELECT CONCAT_WS(': ', time, log)
            FROM $table_name
            ORDER BY id DESC"
        );

        $logs = implode(PHP_EOL, $logs);

        return $logs;
    }

    /**
     *  Clear Log via truncation
     */
    public static function truncate(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'advertising_settings_log';

        $wpdb->query("TRUNCATE TABLE $table_name");

        self::l('Deleted all Logs');
    }
}