<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class SchedulingService extends Service
{
    public function officeHours(array $payload = []): Response
    {
        return $this->post('office-hours', $payload, ['auth' => 'public']);
    }

    public function colors(array $payload = []): Response
    {
        return $this->post('get-colors', $payload, ['auth' => 'public']);
    }

    public function config(array $payload = []): Response
    {
        return $this->post('schedule-config', $payload, ['auth' => 'public']);
    }

    public function book(array $appointment): Response
    {
        return $this->post('book-appointment', $appointment, ['auth' => 'public']);
    }

    public function portal(array $payload): Response
    {
        return $this->post('appointment-portal', $payload, ['auth' => 'public']);
    }

    public function reschedule(array $payload): Response
    {
        return $this->post('appointment-reschedule', $payload, ['auth' => 'public']);
    }

    public function cancel(array $payload): Response
    {
        return $this->post('appointment-cancel', $payload, ['auth' => 'public']);
    }

    public function confirmWaitlistOffer(array $payload): Response
    {
        return $this->post('waitlist-offer', $payload, ['auth' => 'public']);
    }

    public function paymentEvent(array $payload): Response
    {
        return $this->post('scheduling-payment-event', $payload, ['auth' => 'public']);
    }

    public function calendarSyncFeed(array $query): Response
    {
        return $this->get('calendar-sync-feed', $query, ['auth' => 'public']);
    }
}
