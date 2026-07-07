<?php

namespace Tests\Feature;

use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_it_stores_cookie_consent_in_session_and_cookie(): void
    {
        $response = $this->postJson('/cookie-consent', ['consent' => 'accepted']);

        $response->assertOk();
        $response->assertSessionHas('cookie_consent', true);
        $response->assertCookie('cookie_consent', 'accepted');
    }
}
