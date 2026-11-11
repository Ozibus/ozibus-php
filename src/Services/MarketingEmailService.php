<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class MarketingEmailService extends Service
{
    /**
     * Add or restore a consented subscriber within a verified-domain audience.
     */
    public function subscribe(array $subscriber): Response
    {
        return $this->post('newsletter', $subscriber);
    }

    /**
     * Retrieve the authenticated merchant's subscribers for a verified domain.
     */
    public function subscribers(array $query): Response
    {
        return $this->post('get-subscribers', $query);
    }

    /**
     * Suppress a subscriber within the supplied verified-domain audience.
     */
    public function unsubscribe(array $subscriber): Response
    {
        return $this->post('unsubscribe', $subscriber);
    }
}
