<?php

namespace App\Http\Controllers;

use App\Models\FranchiseApplication;
use App\Models\Inquiry;
use App\Services\ApplicationPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function inquiry(Request $request)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(['franchise', 'distribution', 'contact'])], 'name' => 'required|string|max:120', 'mobile' => ['required', 'regex:/^[0-9+() -]{8,20}$/'], 'email' => 'nullable|email|max:255', 'city' => 'required_unless:kind,contact|nullable|string|max:120', 'budget' => ['required_unless:kind,contact', 'nullable', Rule::in(['under_10', '10_25', '25_plus', 'discuss'])], 'message' => 'nullable|string|max:2000', 'locale' => ['required', Rule::in(['mr', 'hi', 'en'])], 'consent' => 'accepted', 'website' => 'nullable|max:0',
        ]);
        unset($data['consent'],$data['website']);
        $item = Inquiry::create($data + ['reference' => (string) Str::uuid(), 'consented_at' => now()]);

        return back()->with('receipt', $item->reference)->with('saved', true);
    }

    public function application(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120', 'mobile' => ['required', 'regex:/^[0-9+() -]{8,20}$/'], 'email' => 'required|email|max:255', 'city' => 'required|string|max:120', 'company_name' => 'nullable|string|max:160', 'trade_name' => 'required|string|max:160', 'has_gst' => ['required', Rule::in(['0', '1'])],
            'gst_number' => ['nullable', 'required_if:has_gst,1', 'prohibited_if:has_gst,0', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'address' => 'required|string|max:2000', 'shop_ownership' => ['required', Rule::in(['owned', 'rented', 'proposed'])], 'shop_area' => 'required|integer|min:1|max:100000', 'shop_address' => 'required|string|max:2000',
            'map_url' => ['nullable', 'url:https', 'max:500', function ($attribute, $value, $fail) {
                $host = strtolower(parse_url($value, PHP_URL_HOST) ?? '');
                $path = parse_url($value, PHP_URL_PATH) ?? '';
                if (! in_array($host, ['maps.google.com', 'maps.app.goo.gl', 'goo.gl']) && ! (in_array($host, ['google.com', 'www.google.com']) && str_starts_with($path, '/maps'))) {
                    $fail('Use a Google Maps location link.');
                }
                if ($host === 'goo.gl' && ! str_starts_with($path, '/maps/')) {
                    $fail('Use a Google Maps location link.');
                }
            }],
            'budget' => ['required', Rule::in(['under_10', '10_25', '25_plus', 'discuss'])], 'experience' => 'nullable|string|max:2000', 'locale' => ['required', Rule::in(['mr', 'hi', 'en'])], 'consent' => 'accepted', 'website' => 'nullable|max:0',
        ] + ApplicationPayments::rules());
        $paymentKeys = array_keys(ApplicationPayments::rules());
        unset($data['consent'],$data['website']);
        foreach ($paymentKeys as $key) {
            unset($data[$key]);
        }
        $application = DB::transaction(function () use ($data, $request) {
            $application = FranchiseApplication::create($data + ['reference' => (string) Str::uuid(), 'consented_at' => now()]);
            $application->refresh();
            ApplicationPayments::record($application, $request);
            ApplicationPayments::event($application, 'application_submitted');

            return $application;
        });
        $statusUrl = URL::temporarySignedRoute('application.status', now()->addDays(30), ['application' => $application->reference]);

        return redirect($statusUrl)->with('receipt', $application->reference)->with('saved', true);
    }
}
