<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public ?string $from_address = null;
    public ?string $from_name = null;
    public ?string $reply_to_address = null;
    public ?string $reply_to_name = null;

    // SMTP configuration
    public string $driver = 'smtp';
    public ?string $host = null;
    public int $port = 587;
    public string $encryption = 'tls';
    public ?string $username = null;
    public ?string $password = null;
    public ?int $timeout = 30;
    public ?string $local_domain = null;

    // Email template and design settings
    public string $template_theme = 'default';
    public string $footer_text = '© Dubai Job Finder. All rights reserved.';
    public string $logo_path = 'sites/email-logo.png';
    public string $primary_color = '#2D2B8D';
    public string $secondary_color = '#FFC903';

    // Email delivery configuration
    public bool $queue_emails = true;
    public string $queue_name = 'emails';
    public string $queue_connection = 'database';
    public array $rate_limiting = [
        'enabled' => true,
        'attempts' => 5,
        'per_minutes' => 1,
    ];

    // Notification settings
    public bool $notifications_enabled = true;
    public array $notification_types = [
        'account' => true,
        'system' => true,
        'marketing' => false,
        'blog' => false,
    ];

    // Email testing and debugging
    public bool $test_mode = false;
    public string $log_channel = 'stack';
    public string $test_to_address = '';

    // Alternative mail providers configuration
    public array $providers = [
        'mailgun' => [
            'domain' => null,
            'secret' => null,
            'endpoint' => 'api.mailgun.net',
        ],
        'postmark' => [
            'token' => null,
        ],
        'ses' => [
            'key' => null,
            'secret' => null,
            'region' => 'us-east-1',
        ],
    ];

    public static function group(): string
    {
        return 'mail';
    }

    public static function encrypted(): array
    {
        return [
            'username',
            'password',
            'providers.mailgun.secret',
            'providers.postmark.token',
            'providers.ses.key',
            'providers.ses.secret',
        ];
    }

    public static function defaults(): array
    {
        return [
            'from_address' => null,
            'from_name' => null,
            'reply_to_address' => null,
            'reply_to_name' => null,
            'driver' => 'smtp',
            'host' => null,
            'port' => 587,
            'encryption' => 'tls',
            'username' => null,
            'password' => null,
            'timeout' => 30,
            'local_domain' => null,
            'template_theme' => 'default',
            'footer_text' => '© ' . date('Y') . ' Dubai Job Finder. All rights reserved.',
            'logo_path' => 'sites/email-logo.png',
            'primary_color' => '#2D2B8D',
            'secondary_color' => '#FFC903',
            'queue_emails' => true,
            'queue_name' => 'emails',
            'queue_connection' => 'database',
            'rate_limiting' => [
                'enabled' => true,
                'attempts' => 5,
                'per_minutes' => 1,
            ],
            'notifications_enabled' => true,
            'notification_types' => [
                'account' => true,
                'system' => true,
                'marketing' => false,
                'blog' => false,
            ],
            'test_mode' => false,
            'log_channel' => 'stack',
            'test_to_address' => '',
            'providers' => [
                'mailgun' => [
                    'domain' => null,
                    'secret' => null,
                    'endpoint' => 'api.mailgun.net',
                ],
                'postmark' => [
                    'token' => null,
                ],
                'ses' => [
                    'key' => null,
                    'secret' => null,
                    'region' => 'us-east-1',
                ],
            ],
        ];
    }

    public static function safe(): self
    {
        try {
            return app(static::class);
        } catch (\Throwable $throwable) {
            return new static(static::defaults());
        }
    }

    public function loadMailSettingsToConfig($data = null): void
    {
        // Core mail configuration
        config([
            'mail.default' => $data['driver'] ?? $this->driver,
            'mail.mailers.smtp.host' => $data['host'] ?? $this->host,
            'mail.mailers.smtp.port' => $data['port'] ?? $this->port,
            'mail.mailers.smtp.encryption' => $data['encryption'] ?? $this->encryption,
            'mail.mailers.smtp.username' => $data['username'] ?? $this->username,
            'mail.mailers.smtp.password' => $data['password'] ?? $this->password,
            'mail.mailers.smtp.timeout' => $data['timeout'] ?? $this->timeout,
            'mail.mailers.smtp.local_domain' => $data['local_domain'] ?? $this->local_domain,
            'mail.from.address' => $data['from_address'] ?? $this->from_address,
            'mail.from.name' => $data['from_name'] ?? $this->from_name,
        ]);

        // Reply-to configuration
        if (isset($data['reply_to_address']) || $this->reply_to_address) {
            config([
                'mail.reply_to.address' => $data['reply_to_address'] ?? $this->reply_to_address,
                'mail.reply_to.name' => $data['reply_to_name'] ?? $this->reply_to_name,
            ]);
        }

        // Queue configuration
        if ($this->queue_emails) {
            config([
                'queue.connections.' . $this->queue_connection . '.queue' => $this->queue_name,
                'mail.queue.connection' => $this->queue_connection,
                'mail.queue.queue' => $this->queue_name,
            ]);
        }

        // Configure alternative mail providers if driver matches
        if ($this->driver === 'mailgun' && isset($this->providers['mailgun'])) {
            config([
                'services.mailgun.domain' => $this->providers['mailgun']['domain'],
                'services.mailgun.secret' => $this->providers['mailgun']['secret'],
                'services.mailgun.endpoint' => $this->providers['mailgun']['endpoint'],
            ]);
        } elseif ($this->driver === 'postmark' && isset($this->providers['postmark'])) {
            config([
                'services.postmark.token' => $this->providers['postmark']['token'],
            ]);
        } elseif ($this->driver === 'ses' && isset($this->providers['ses'])) {
            config([
                'services.ses.key' => $this->providers['ses']['key'],
                'services.ses.secret' => $this->providers['ses']['secret'],
                'services.ses.region' => $this->providers['ses']['region'],
            ]);
        }

        // Test mode configuration
        if ($this->test_mode) {
            config([
                'mail.to.address' => $this->test_to_address,
                'mail.to.name' => 'Test Recipient',
            ]);
        }
    }

    /**
     * Check if MailSettings is configured with necessary values.
     */
    public function isMailSettingsConfigured(): bool
    {
        // Basic configuration check
        $hasBasicConfig = $this->from_address && $this->from_name;

        // Driver-specific validation
        if ($this->driver === 'smtp') {
            return $hasBasicConfig && $this->host && $this->username && $this->password;
        } elseif ($this->driver === 'mailgun') {
            return $hasBasicConfig && isset($this->providers['mailgun']['domain']) && isset($this->providers['mailgun']['secret']);
        } elseif ($this->driver === 'postmark') {
            return $hasBasicConfig && isset($this->providers['postmark']['token']);
        } elseif ($this->driver === 'ses') {
            return $hasBasicConfig && isset($this->providers['ses']['key']) && isset($this->providers['ses']['secret']);
        }

        return $hasBasicConfig;
    }

    /**
     * Get email theme configuration for templates
     */
    public function getEmailThemeConfig(): array
    {
        return [
            'logo' => $this->logo_path,
            'primaryColor' => $this->primary_color,
            'secondaryColor' => $this->secondary_color,
            'footer' => $this->footer_text,
            'theme' => $this->template_theme,
        ];
    }

    /**
     * Check if a specific notification type is enabled
     */
    public function isNotificationTypeEnabled(string $type): bool
    {
        return $this->notifications_enabled &&
               isset($this->notification_types[$type]) &&
               $this->notification_types[$type];
    }

    /**
     * Get rate limiting configuration
     */
    public function getRateLimitingConfig(): array
    {
        return $this->rate_limiting;
    }
}
