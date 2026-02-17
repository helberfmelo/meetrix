<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Assuming user has one tenant for now, or we get tenant from context
        // For MVP, let's say user->tenant relationship exists or we find the tenant for the user
        $tenant = Tenant::where('user_id', $user->id)->first();
        
        if (!$tenant) {
             // Auto-create tenant for new users if not exists (Onboarding logic might handle this, but fail-safe)
             $tenant = Tenant::create([
                'user_id' => $user->id,
                'name' => $user->name . "'s Team",
                'slug' => Str::slug($user->name . ' ' . Str::random(4)),
             ]);
        }

        $pages = Page::where('tenant_id', $tenant->id)->get();

        return response()->json($pages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();
        $tenant = Tenant::where('user_id', $user->id)->firstOrFail();

        $page = Page::create([
            'tenant_id' => $tenant->id,
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_active' => true,
            'config' => [], // Default empty config
        ]);

        return response()->json($page, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        // Publicly accessible details
        return response()->json([
            'title' => $page->title,
            'description' => $page->description,
            'slug' => $page->slug,
            'config' => $page->config,
            'tenant_name' => $page->tenant->name,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $tenant = Tenant::where('user_id', $user->id)->firstOrFail();
        
        $page = Page::where('id', $id)->where('tenant_id', $tenant->id)->firstOrFail();

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $page->update($request->only(['title', 'description', 'config', 'is_active']));

        return response()->json($page);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Implement soft delete or hard delete
    }
}
