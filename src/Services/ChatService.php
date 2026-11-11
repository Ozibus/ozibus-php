<?php

namespace Ozibus\Sdk\Services;

use Ozibus\Sdk\Response;

class ChatService extends Service
{
    public function widgetConfig(array $payload = []): Response
    {
        return $this->post('widget-config', $payload, ['auth' => 'public']);
    }

    public function event(array $payload): Response
    {
        return $this->post('widget-event', $payload, ['auth' => 'public']);
    }

    public function submit(array $message): Response
    {
        return $this->post('conversation-submit', $message, ['auth' => 'public']);
    }

    public function history(array $payload): Response
    {
        return $this->post('conversation-history', $payload, ['auth' => 'public']);
    }

    public function knowledgeBase(array $payload = []): Response
    {
        return $this->post('knowledge-base', $payload, ['auth' => 'public']);
    }

    public function uploadFile(array $payload): Response
    {
        return $this->post('upload-file', $payload, ['auth' => 'public']);
    }

    public function deleteFile(array $payload): Response
    {
        return $this->post('delete-file', $payload, ['auth' => 'public']);
    }

    public function close(array $payload): Response
    {
        return $this->post('close-chat', $payload, ['auth' => 'public']);
    }

    public function emailTranscript(array $payload): Response
    {
        return $this->post('email-transcript', $payload, ['auth' => 'public']);
    }

    public function syncHistory(array $payload): Response
    {
        return $this->post('sync-history', $payload, ['auth' => 'public']);
    }

    public function aiReply(array $payload): Response
    {
        return $this->post('ai-chatbot-reply', $payload, ['auth' => 'public']);
    }

    public function billingSummary(array $payload): Response
    {
        return $this->post('chat-billing-summary', $payload);
    }
}
