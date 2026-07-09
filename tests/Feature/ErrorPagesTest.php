<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_page_expired_error_uses_recovery_page(): void
    {
        Route::get('/__test/page-expired', fn () => abort(419));

        $response = $this->get('/__test/page-expired');

        $response->assertStatus(419);
        $response->assertSee('Session expired');
        $response->assertSee('Please refresh and try again.');
        $response->assertSee('Register');
    }
}
