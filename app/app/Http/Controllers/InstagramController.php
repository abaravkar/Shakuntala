<?php
namespace App\Http\Controllers;
use App\Services\InstagramFeed;
use Illuminate\Support\Facades\Cache;
class InstagramController extends Controller {
    public function settings(InstagramFeed $feed) { return view('admin.instagram',['feed'=>$feed->feed(),'status'=>Cache::get('instagram.status','Not connected.'),'configured'=>(bool)config('instagram.token')]); }
    public function sync(InstagramFeed $feed) { $ok=$feed->sync(); return back()->with('message',$ok ? 'Instagram feed synced.' : 'Instagram could not sync. See connection status below.'); }
}
