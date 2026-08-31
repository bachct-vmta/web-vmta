<?php

namespace Tests\Feature\Chatbot;

use Mockery\MockInterface;
use Packages\Chatbot\Src\Services\TourismApiClient;
use Packages\Core\Src\Http\Middleware\PermissionMiddleware;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminConversationMarkdownTest extends TestCase
{
    public function test_admin_conversation_renders_sanitized_assistant_markdown(): void
    {
        $this->withoutMiddleware(PermissionMiddleware::class);

        $this->mock(TourismApiClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getConversation')
                ->once()
                ->with('markdown-conversation')
                ->andReturn([
                    [
                        'role' => 'assistant',
                        'content' => "**Bold bot reply**\n\n- First item\n\n<script>alert('xss')</script>",
                    ],
                    [
                        'role' => 'user',
                        'content' => '**User markdown stays text**',
                    ],
                ]);
        });

        $response = $this->actingAs($this->admin())
            ->get('/admin/chatbot/conversations/markdown-conversation');

        $response->assertOk();
        $response->assertSee('<strong>Bold bot reply</strong>', false);
        $response->assertSee('<li>First item</li>', false);
        $response->assertSee('**User markdown stays text**');
        $response->assertDontSee("<script>alert('xss')</script>", false);
        $response->assertDontSee("alert('xss')", false);
    }

    private function admin(): User
    {
        return User::factory()->make([
            'name' => 'Admin',
            'is_super_user' => true,
        ]);
    }
}
