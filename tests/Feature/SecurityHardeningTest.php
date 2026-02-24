<?php

use App\Models\Company;
use App\Models\Projects;
use App\Models\User;
use App\Models\WikiPage;
use Illuminate\Support\Facades\RateLimiter;

test('login is rate limited after too many failed attempts', function () {
    $email = 'ratelimit@example.test';

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->from('/login')->post('/login', [
        'email' => $email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');

    RateLimiter::clear(mb_strtolower($email).'|127.0.0.1');
});

test('security headers are added to responses', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
});

test('auth events are persisted to security audit logs', function () {
    $company = Company::create([
        'name' => 'Security Co',
        'domain' => 'security.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Security User',
        'company_id' => $company->id,
        'password' => 'password',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('security_audit_logs', [
        'event_type' => 'auth.login.success',
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertDatabaseHas('security_audit_logs', [
        'event_type' => 'auth.logout',
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
});

test('wiki markdown output strips unsafe html', function () {
    $company = Company::create([
        'name' => 'Wiki Security Co',
        'domain' => 'wiki-security.local',
        'owner_id' => 1,
        'plan' => 'pro',
    ]);

    $user = User::factory()->create([
        'full_name' => 'Wiki Security User',
        'company_id' => $company->id,
    ]);

    $project = Projects::create([
        'name' => 'Wiki Project',
        'description' => 'Wiki project description',
        'company_id' => $company->id,
    ]);

    $page = WikiPage::create([
        'project_id' => $project->id,
        'created_by' => $user->id,
        'updated_by' => $user->id,
        'title' => 'Dangerous page',
        'slug' => 'dangerous-page',
        'content' => "<script>alert('xss')</script>\n\n# Safe title",
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.projects.wiki.api.show', ['project' => $project->id, 'wikiPage' => $page->id]));

    $response->assertOk();
    $response->assertJsonMissing(['rendered_content' => "<script>alert('xss')</script>"]);
    expect((string) $response->json('rendered_content'))->not->toContain('<script>');
});

test('forms include csrf token fields', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('name="_token"', false);
});
