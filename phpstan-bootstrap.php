<?php

// Larastan boots the Laravel app to analyse it. The CI SAST job runs without a
// .env, so give the booted app a throwaway key instead of crashing on models
// that touch the encrypter.
if (empty(config('app.key'))) {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
}
