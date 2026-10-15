<?php

namespace Tests\Feature;

use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_wizards_and_entry_links_are_available(): void
    {
        $this->seed();
        foreach (['/franchise', '/distribution', '/contact', '/partners/onboarding'] as $url) {
            $this->get($url)->assertOk()->assertSee('href="/franchise/application"', false)->assertSee('href="/distribution/application"', false);
        }
        $this->get('/distribution/application')->assertOk()->assertSee('data-wizard', false)->assertSee('name="kind" value="distribution"', false)->assertSee('id="application-review"', false);
        $this->get('/partners/onboarding')->assertSee('Verified payment unlocks team review');
    }

    public function test_all_public_pages_render_and_hidden_legal_links_stay_at_payment(): void
    {
        $this->seed();
        foreach (['/', '/about', '/menu', '/locations', '/franchise', '/distribution', '/moments', '/blog', '/contact', '/instagram', '/social', '/franchise/application', '/franchise/agreement', '/terms', '/privacy', '/refund'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertDontSee('href="/terms"', false)->assertDontSee('href="/privacy"', false)->assertDontSee('href="/refund"', false)->assertSee('Rabdi Mastani');
        $this->get('/franchise/application')->assertSee('href="/terms"', false)->assertSee('href="/privacy"', false)->assertSee('href="/refund"', false);
        $this->get('/locations')->assertDontSee('Phaltan')->assertDontSee('Indapur')->assertSee('Planned');
        $this->get('/unknown')->assertNotFound();
    }

    public function test_valid_inquiry_is_stored_and_untrusted_status_is_ignored(): void
    {
        $this->post('/inquiries', ['kind' => 'distribution', 'name' => 'Test Partner', 'mobile' => '9876543210', 'email' => 'partner@example.com', 'city' => 'Pune', 'budget' => '10_25', 'message' => 'Distribution enquiry', 'locale' => 'en', 'consent' => '1', 'status' => 'approved'])
            ->assertRedirect()->assertSessionHas('receipt');
        $this->assertDatabaseHas('inquiries', ['name' => 'Test Partner', 'kind' => 'distribution', 'status' => 'new']);
    }

    public function test_consent_and_investment_are_required_and_honeypot_is_rejected(): void
    {
        $data = ['kind' => 'franchise', 'name' => 'Test', 'mobile' => '9876543210', 'city' => 'Pune', 'locale' => 'mr'];
        $this->post('/inquiries', $data)->assertSessionHasErrors(['budget', 'consent']);
        $this->post('/inquiries', $data + ['budget' => 'under_10', 'consent' => '1', 'website' => 'spam'])->assertSessionHasErrors('website');
        $this->assertDatabaseCount('inquiries', 0);
    }

    private function applicationData(): array
    {
        return ['name' => 'Test Applicant', 'mobile' => '9876543210', 'email' => 'applicant@example.com', 'city' => 'Pune', 'trade_name' => 'Sweet Shop', 'has_gst' => '0', 'address' => 'Contact address', 'shop_ownership' => 'rented', 'shop_area' => 400, 'shop_address' => 'Shop address', 'map_url' => 'https://maps.app.goo.gl/example', 'budget' => '10_25', 'locale' => 'en', 'consent' => '1'];
    }

    public function test_application_is_saved_without_client_control_of_payment_or_fee(): void
    {
        $this->post('/franchise/applications', $this->applicationData() + ['payment_status' => 'paid', 'fee_paise' => 1, 'status' => 'signed'])->assertRedirect()->assertSessionHas('receipt');
        $this->assertDatabaseHas('franchise_applications', ['email' => 'applicant@example.com', 'payment_status' => 'pending', 'status' => 'awaiting_payment', 'fee_paise' => 500000]);
    }

    public function test_application_requires_gst_when_registered_and_a_real_maps_host(): void
    {
        $data = $this->applicationData();
        $data['has_gst'] = '1';
        $data['map_url'] = 'https://maps.app.goo.gl.attacker.example/location';
        $this->post('/franchise/applications', $data)->assertSessionHasErrors(['gst_number', 'map_url']);
        $this->assertDatabaseCount('franchise_applications', 0);
    }

    public function test_admin_requires_privilege_and_can_manage_content(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->get('/admin')->assertOk();
        $this->post('/admin/content', ['type' => 'blog', 'slug' => 'approved-story', 'title' => ['en' => 'Approved story', 'mr' => 'मंजूर कथा', 'hi' => 'स्वीकृत कहानी'], 'body' => ['en' => 'A real update', 'mr' => 'बातमी', 'hi' => 'समाचार'], 'status' => 'published', 'sort_order' => 1])->assertRedirect('/admin');
        $this->get('/blog')->assertSee('Approved story');
        $this->put('/admin/content/'.ContentItem::first()->id, ['type' => 'social', 'slug' => 'unsafe', 'title' => ['en' => 'Test', 'mr' => 'Test', 'hi' => 'Test'], 'body' => ['en' => '', 'mr' => '', 'hi' => ''], 'status' => 'published', 'sort_order' => 0, 'url' => 'javascript:alert(1)'])->assertSessionHasErrors('url');
    }

    public function test_admin_login_rejects_regular_users_and_regenerates_authenticated_session(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.com', 'password' => 'test-password-123']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'test-password-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->forceFill(['is_admin' => true])->save();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'test-password-123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_submission_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/inquiries', [])->assertRedirect();
        }
        $this->post('/inquiries', [])->assertStatus(429);
    }

    public function test_admin_views_and_application_details_are_protected(): void
    {
        $this->seed();
        $this->post('/franchise/applications', $this->applicationData())->assertSessionHas('receipt');
        $this->get('/admin/applications/1')->assertRedirect('/admin/login');
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->get('/admin/content/new')->assertOk();
        $this->get('/admin/content/1/edit')->assertOk();
        $this->get('/admin/applications/1')->assertOk()->assertSee('Sweet Shop')->assertSee('pending');
    }

    public function test_cms_changes_reach_home_menu_locations_and_both_galleries(): void
    {
        $this->seed();
        $product = ContentItem::where('slug', 'mastani')->first();
        $product->update(['price' => 123]);
        $this->get('/')->assertSee('₹123');
        $this->get('/menu')->assertSee('₹123');
        ContentItem::where('slug', 'baramati-subhash')->update(['status' => 'closed']);
        $this->get('/')->assertDontSee('Subhash Chowk');
        $this->get('/locations')->assertDontSee('Subhash Chowk');
        ContentItem::where('slug', 'gallery-customer')->update(['status' => 'draft']);
        foreach (['/', '/moments', '/instagram'] as $url) {
            $this->get($url)->assertDontSee('assets/customer.webp', false);
        }
    }

    public function test_published_content_requires_the_assets_price_and_link_it_uses(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
        $data = ['slug' => 'new-content', 'title' => ['en' => 'Example', 'mr' => 'उदाहरण', 'hi' => 'उदाहरण'], 'body' => ['en' => '', 'mr' => '', 'hi' => ''], 'status' => 'published', 'sort_order' => 0];
        $this->post('/admin/content', $data + ['type' => 'product'])->assertSessionHasErrors(['image', 'price']);
        $this->post('/admin/content', $data + ['type' => 'gallery'])->assertSessionHasErrors('image');
        $this->post('/admin/content', $data + ['type' => 'social'])->assertSessionHasErrors('url');
        $this->assertDatabaseCount('content_items', 0);
    }

    public function test_homepage_contact_form_submits_and_keeps_validation_values(): void
    {
        $this->seed();
        $this->get('/')->assertSee('action="/inquiries"', false)->assertSee('name="kind" value="contact"', false);
        $this->from('/')->post('/inquiries', ['kind' => 'contact', 'name' => 'Website Visitor', 'mobile' => 'bad', 'locale' => 'en', 'consent' => 1])->assertRedirect('/')->assertSessionHasErrors('mobile')->assertSessionHasInput('name', 'Website Visitor');
        $this->from('/')->post('/inquiries', ['kind' => 'contact', 'name' => 'Website Visitor', 'mobile' => '9876543210', 'locale' => 'en', 'consent' => 1])->assertRedirect('/')->assertSessionHas('receipt');
        $this->assertDatabaseHas('inquiries', ['kind' => 'contact', 'name' => 'Website Visitor']);
    }
}
