<?php

namespace Tests\Feature;

use App\Models\Generator;
use App\Models\User;
use Tests\TestCase;

class GeneratorEditFillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'filamentcraft',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => 'hello',
        ]);
    }

    public function test_generator_edit_page_renders_with_filled_data(): void
    {
        $user = User::first();
        $this->assertNotNull($user);
        
        $generator = Generator::first();
        $this->assertNotNull($generator);

        $url = \App\Filament\Resources\Generators\GeneratorResource::getUrl('edit', ['record' => $generator]);
        dump('URL: ' . $url);

        $response = $this->actingAs($user)
            ->get($url);
        
        dump('Status: ' . $response->getStatusCode());
        
        $response->assertStatus(200);
    }
}
