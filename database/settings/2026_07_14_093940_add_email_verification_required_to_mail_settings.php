<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.email_verification_required', true);
    }

    public function down(): void
    {
        $this->migrator->delete('mail.email_verification_required');
    }
};
