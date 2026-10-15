<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SiteController extends Controller
{
    public function page(Request $request, string $page = 'index')
    {
        abort_unless(in_array($page, ['index', 'about', 'menu', 'locations', 'franchise', 'distribution', 'moments', 'blog', 'contact', 'instagram', 'social', 'application', 'distribution-application', 'onboarding', 'terms', 'privacy', 'refund', 'agreement']), 404);
        $locale = $request->cookie('shakuntala_locale', 'mr');
        if (! in_array($locale, ['mr', 'hi', 'en'])) {
            $locale = 'mr';
        }
        $items = ContentItem::orderBy('sort_order')->orderBy('id')->get();
        View::share(compact('locale', 'page', 'items'));
        View::share('paymentSettings', PaymentSetting::details());
        if ($page === 'instagram') View::share('instagramFeed', app(\App\Services\InstagramFeed::class)->feed());
        if ($page === 'franchise') View::share('franchiseModels', \App\Models\FranchiseModel::where('published', true)->orderBy('sort_order')->orderBy('id')->get());

        return view('pages.'.$page);
    }
}
