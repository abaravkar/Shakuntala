<?php
namespace Tests\Feature;
use App\Services\InstagramFeed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
class InstagramFeedTest extends TestCase {
    protected function setUp(): void { parent::setUp(); Cache::forget('instagram.feed'); Cache::forget('instagram.status'); }
    public function test_missing_token_and_wrong_account_do_not_publish(): void {
        config(['instagram.token'=>null]); Http::fake(); $feed=new InstagramFeed;
        $this->assertFalse($feed->sync()); Http::assertNothingSent();
        config(['instagram.token'=>'private-test-token']);
        Http::fake(['https://graph.instagram.com/*/me?*'=>Http::response(['id'=>'123','username'=>'wrong_account'])]);
        $this->assertFalse($feed->sync()); $this->assertNull($feed->feed());
        $this->assertStringNotContainsString('private-test-token',Cache::get('instagram.status'));
    }
    public function test_verified_feed_uses_server_token_and_retains_cache_on_failure(): void {
        config(['instagram.token'=>'private-test-token']);
        Http::fakeSequence()->push(['id'=>'123','username'=>'shakuntala_rabadi_mastani'])->push(['data'=>[['caption'=>'Sweet day','media_type'=>'VIDEO','thumbnail_url'=>'https://cdn.example.test/photo.jpg','permalink'=>'https://www.instagram.com/p/test/'],['media_url'=>'https://cdn.example.test/a.jpg','permalink'=>'https://evil.example.test/']]])->push(['error'=>'private-test-token'],401);
        $feed=new InstagramFeed; $this->assertTrue($feed->sync());
        $this->assertCount(1,$feed->feed()['posts']);
        Http::assertSent(fn($r)=>$r->hasHeader('Authorization','Bearer private-test-token')&&!str_contains($r->url(),'private-test-token'));

        $this->assertFalse($feed->sync()); $this->assertCount(1,$feed->feed()['posts']);
        $this->assertStringNotContainsString('private-test-token',Cache::get('instagram.status'));
    }
}


