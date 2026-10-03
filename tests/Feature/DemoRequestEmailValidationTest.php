<?php

namespace Tests\Feature;

use App\Models\DemoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoRequestEmailValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_request_persists_document_and_phone_without_masks(): void
    {
        $this->postJson(route('demo-requests.create'), [
            'name' => 'Restaurante Teste',
            'document' => '11.222.333/0001-81',
            'email' => 'demo@gmail.com',
            'phone' => '(85) 98855-4489',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('demo_requests', [
            'document' => '11222333000181',
            'phone' => '85988554489',
            'email' => 'demo@gmail.com',
        ]);
    }

    public function test_demo_request_rejects_an_invalid_email_address(): void
    {
        $this->postJson(route('demo-requests.create'), [
            'name' => 'Restaurante Teste',
            'document' => '11222333000181',
            'email' => 'nao-e-email',
            'phone' => '85988554489',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('demo_requests', 0);
    }

    public function test_duplicate_email_returns_a_clear_field_error(): void
    {
        DemoRequest::create([
            'name' => 'Restaurante Existente',
            'document' => '11222333000181',
            'email' => 'demo@gmail.com',
            'phone' => '85988554489',
            'status' => 'created',
        ]);

        $this->postJson(route('demo-requests.create'), [
            'name' => 'Outro Restaurante',
            'document' => '22333444000182',
            'email' => 'demo@gmail.com',
            'phone' => '85988554489',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Este e-mail já está cadastrado. Informe outro endereço.');
    }
}
