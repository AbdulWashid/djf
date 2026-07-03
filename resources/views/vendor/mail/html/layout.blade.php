<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

@php
    $settings = app(\App\Settings\MailSettings::class);
    $themeName = $settings->template_theme ?? 'default';
    $primaryColor = $settings->primary_color ?? '#2D2B8D';
    $secondaryColor = $settings->secondary_color ?? '#FFC903';
    $footerColor = '#b0adc5';
@endphp

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        :root {
            --mail-primary: {{ $primaryColor }};
            --mail-secondary: {{ $secondaryColor }};
            --mail-background: #edf2f7;
            --mail-surface: #ffffff;
            --mail-text: #718096;
            --mail-strong-text: #3d4852;
            --mail-footer: {{ $footerColor }};
            --mail-border: #e8e5ef;
        }

        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        body.mail-theme-dark {
            background-color: #222222 !important;
            color: #eaeaea !important;
        }

        body.mail-theme-dark .wrapper,
        body.mail-theme-dark .body {
            background-color: #222222 !important;
        }

        body.mail-theme-dark .inner-body {
            background-color: #333333 !important;
            border-color: #444444 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
        }

        body.mail-theme-dark h1,
        body.mail-theme-dark h2,
        body.mail-theme-dark h3,
        body.mail-theme-dark p,
        body.mail-theme-dark ul,
        body.mail-theme-dark ol,
        body.mail-theme-dark blockquote {
            color: #eaeaea !important;
        }

        body.mail-theme-dark .footer p {
            color: #999999 !important;
        }

        body.mail-theme-minimal .inner-body {
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        body.mail-theme-minimal .header {
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--mail-border) !important;
        }

        body.mail-theme-minimal .header a {
            color: var(--mail-primary) !important;
        }

        body.mail-theme-modern .inner-body {
            border-radius: 12px !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
        }

        body.mail-theme-corporate .inner-body {
            border-radius: 0 !important;
        }

        body.mail-theme-corporate .title {
            border-bottom: 2px solid var(--mail-secondary) !important;
            padding-bottom: 10px !important;
        }

        body.mail-theme-dark .button-primary,
        body.mail-theme-dark .button-blue {
            background-color: var(--mail-secondary) !important;
            border-color: var(--mail-secondary) !important;
            color: #111111 !important;
        }
    </style>
    {!! $head ?? '' !!}
</head>

<body class="mail-theme-{{ $themeName }}">

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {!! $header ?? '' !!}

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0"
                            style="border: hidden !important;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                        {!! $subcopy ?? '' !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {!! $footer ?? '' !!}
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
