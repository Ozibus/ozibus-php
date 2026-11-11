# Publish Ozibus PHP SDK to Packagist

## Package metadata

- Composer package name: `ozibus/ozibus-php`
- PHP namespace: `Ozibus\Sdk\`
- Package type: `library`
- Minimum PHP version: 7.4
- Required PHP extension: JSON
- Recommended extension: cURL (streams fallback)
- License: `proprietary` (review `LICENSE` before release)
- Existing SDK version: 1.3.0 (version is intentionally omitted from `composer.json`)

## Checklist

1. Review the source and license, confirm rights to publish the SDK publicly. Public Packagist packages expose source code through the public repository. If the SDK must remain private, use a private Composer registry instead.
2. Make sure the repository name, description, default branch, and public documentation links suit your branding. No repository URL was assumed or inserted into `composer.json`.
3. Install PHP 7.4+ and Composer 2 locally. From the repository root run:

   ```bash
   composer validate --strict
   php tests/smoke.php
   ```

4. Create a **public** repository in your own GitHub/GitLab account, e.g. `ozibus-php-sdk`. Copy these package files into the repository **root** (not a nested folder). Do not upload secrets, API keys, `.env`, or `vendor`.
5. Commit and tag the SDK's current version (only if `v1.3.0` does not already exist):

   ```bash
   git init
   git add .
   git commit -m "Prepare Ozibus PHP SDK for Packagist"
   git branch -M main
   git remote add origin https://github.com/YOUR_ORG/ozibus-php-sdk.git
   git push -u origin main
   git tag v1.3.0
   git push origin v1.3.0
   ```

   Replace `YOUR_ORG` with your actual organization. If the repository already has Git history, use its established workflow instead of reinitializing Git. Never overwrite an existing version tag.
6. Sign in to https://packagist.org and select **Submit**; paste the repository's public HTTPS Git URL. Confirm the registered package name is `ozibus/ozibus-php` (availability is not guaranteed).
7. Enable/verify automatic package updates through Packagist's GitHub integration or webhook instructions on the package page.
8. Test in a **separate** application after indexing:

   ```bash
   composer require ozibus/ozibus-php:^1.3
   ```

9. Release subsequent versions by updating SDK source/version markers as needed, checking compatibility, committing, and pushing a *new* tag such as `v1.3.1`. Never edit/reuse released tags.

## Local smoke test without network

`php tests/smoke.php` exercises class loading and constructing the SDK without any live API request. PHP lint alone does not verify PHP 7.4 parser compatibility if run on a newer runtime; CI includes a PHP 7.4 job to cover that.

## Typical integration

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Ozibus\Sdk\Client;

$client = Client::make([
    'merchant_id' => getenv('OZIBUS_MERCHANT_ID'),
    'secret_key' => getenv('OZIBUS_SECRET_KEY'),
]);

// For example, use $client->sms() or $client->email().
```

Never expose merchant secret keys or bearer tokens in a browser.
