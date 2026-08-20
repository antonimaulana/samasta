<?php

namespace Tests\Feature;

use App\Mail\OperationalDigestMail;
use App\Models\User;
use App\Support\OperationalDigestDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OperationalDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_download_dashboard_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension required for PDF export.');
        }

        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $response = $this->actingAs($viewer)
            ->get(route('admin.dashboard.export-pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_digest_command_skips_when_disabled(): void
    {
        config(['alerts.digest.enabled' => false]);

        $this->artisan('operational:send-digest')
            ->expectsOutputToContain('Digest email nonaktif')
            ->assertSuccessful();
    }

    public function test_digest_sends_mail_to_admin_and_operator(): void
    {
        Mail::fake();
        config(['alerts.digest.enabled' => true, 'alerts.digest.recipients' => '']);

        User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@test.local']);
        User::factory()->create(['role' => User::ROLE_OPERATOR, 'email' => 'ops@test.local']);

        $sent = app(OperationalDigestDispatcher::class)->dispatch();

        $this->assertSame(2, $sent);
        Mail::assertSent(OperationalDigestMail::class, 1);
        Mail::assertSent(OperationalDigestMail::class, function (OperationalDigestMail $mail) {
            return count($mail->to) === 2;
        });
    }
}
