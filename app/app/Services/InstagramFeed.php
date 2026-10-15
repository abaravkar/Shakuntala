<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
class InstagramFeed {
    public function feed(): ?array { return Cache::get('instagram.feed'); }
    public function sync(): bool {
        $token=config('instagram.token');
        if (!$token) { Cache::put('instagram.status','Access token not configured.'); return false; }
        try {
            $base='https://graph.instagram.com/'.config('instagram.version');
            $http=Http::withToken($token)->connectTimeout(5)->timeout(15);
            $profile=$http->get($base.'/me',['fields'=>'id,username'])->throw()->json();
            if (($profile['username'] ?? '')!==config('instagram.username') || !preg_match('/^\d+$/',(string)($profile['id'] ?? ''))) {
                Cache::put('instagram.status','The token does not belong to the configured brand account.'); return false;
            }
            $media=$http->get($base.'/'.$profile['id'].'/media',['fields'=>'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp','limit'=>12])->throw()->json();
            if (!is_array($media['data'] ?? null)) throw new \RuntimeException('Invalid response');
            $posts=[];
            foreach($media['data'] as $post) {
                $image=($post['media_type'] ?? '')==='VIDEO' ? ($post['thumbnail_url'] ?? null) : ($post['media_url'] ?? null);
                $link=$post['permalink'] ?? '';
                if (!$image || parse_url($image,PHP_URL_SCHEME)!=='https' || parse_url($link,PHP_URL_SCHEME)!=='https' || !in_array(parse_url($link,PHP_URL_HOST),['www.instagram.com','instagram.com'])) continue;
                $posts[]=['image'=>$image,'url'=>$link,'caption'=>mb_substr($post['caption'] ?? '',0,1000),'type'=>$post['media_type'] ?? 'IMAGE'];
            }
            Cache::put('instagram.feed',['posts'=>$posts,'synced_at'=>now()->toIso8601String()],now()->addHours(24));
            Cache::put('instagram.status','Connected. Latest posts synced.'); return true;
        } catch (\Throwable $e) {
            // Never expose provider responses, request URLs or credentials.
            Cache::put('instagram.status','Sync failed. Check token expiry, account access and API permissions.'); return false;
        }
    }
}
