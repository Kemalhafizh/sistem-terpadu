<?php

test('public about page is accessible', function () {
    $response = $this->get(route('public.about'));
    $response->assertStatus(200);
});

test('public contact page is accessible', function () {
    $response = $this->get(route('public.contact'));
    $response->assertStatus(200);
});

test('public facilities page is accessible', function () {
    $response = $this->get(route('public.facilities'));
    $response->assertStatus(200);
});

test('public achievements page is accessible', function () {
    $response = $this->get(route('public.achievements'));
    $response->assertStatus(200);
});

test('public staff directory page is accessible', function () {
    $response = $this->get(route('public.staff-directory'));
    $response->assertStatus(200);
});

test('public virtual tour page is accessible', function () {
    $response = $this->get(route('public.virtual-tour'));
    $response->assertStatus(200);
});
