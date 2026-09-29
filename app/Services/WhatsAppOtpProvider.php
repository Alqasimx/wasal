<?php

namespace App\Services;

interface WhatsAppOtpProvider
{
    public function send(string $phone, string $code): void;
}