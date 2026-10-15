<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\FranchiseApplication;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($credentials + ['is_admin' => true])) {
            return back()->withErrors(['email' => 'The sign-in details are incorrect.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    public function dashboard()
    {
        return view('admin.dashboard', ['inquiries' => Inquiry::latest()->paginate(15, ['*'], 'inquiries'), 'applications' => FranchiseApplication::latest()->paginate(15, ['*'], 'applications'), 'items' => ContentItem::orderBy('type')->orderBy('sort_order')->get()]);
    }

    public function edit(?ContentItem $item = null)
    {
        return view('admin.content', ['item' => $item ?? new ContentItem]);
    }

    public function save(Request $request, ?ContentItem $item = null)
    {
        $item ??= new ContentItem;
        $data = $request->validate([
            'type' => ['required', Rule::in(['product', 'branch', 'blog', 'social', 'gallery'])], 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('content_items')->ignore($item->id)], 'title' => 'required|array:mr,hi,en', 'title.*' => 'required|string|max:255', 'body' => 'required|array:mr,hi,en', 'body.*' => 'nullable|string|max:10000',
            'image' => ['nullable', Rule::requiredIf(fn () => in_array($request->input('type'), ['product', 'gallery']) && $request->input('status') === 'published'), 'regex:/^[a-zA-Z0-9_-]+\.(webp|png|jpg|jpeg)$/', function ($attribute, $value, $fail) {
                if ($value && ! is_file(public_path('assets/'.$value))) {
                    $fail('Choose an image already available in public/assets.');
                }
            }],
            'price' => ['nullable', Rule::requiredIf(fn () => $request->input('type') === 'product' && $request->input('status') === 'published'), 'integer', 'min:0', 'max:1000000'], 'status' => ['required', Rule::in(['draft', 'published', 'reported', 'planned', 'unverified', 'closed'])], 'url' => ['nullable', Rule::requiredIf(fn () => $request->input('type') === 'social' && $request->input('status') === 'published'), 'url:https', 'max:500'], 'sort_order' => 'required|integer|min:0|max:10000',
        ]);
        $item->fill($data)->save();

        return redirect('/admin')->with('message', 'Content saved.');
    }

    public function status(Request $request, Inquiry $inquiry)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['new', 'reviewing', 'contacted', 'closed'])]]);
        $inquiry->update($data);

        return back()->with('message', 'Inquiry updated.');
    }

    public function application(FranchiseApplication $application)
    {
        return view('admin.application', compact('application'));
    }
}
