<?php

namespace App\Http\Controllers;

use App\Models\ProductVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductVersionController extends Controller
{
    // Index - list all product versions
    public function index()
    {
        $productVersions = ProductVersion::orderBy('release_date', 'desc')->get();

        return Inertia::render('ProductVersions/Index', [
            'productVersions' => $productVersions
        ]);
    }

    // Create - show form
    public function create()
    {
        return Inertia::render('ProductVersions/Create');
    }

    // Store - save new version
    public function store(Request $request)
    {
        $data = $request->validate([
            'version_name' => 'required|string|max:255|unique:product_versions,version_name',
            'release_date' => 'required|date',
            'environment' => 'required|in:production,staging,development',
            'release_notes' => 'nullable|string',
            'build_id' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:255',
            'db_schema_version' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        ProductVersion::create($data);

        return redirect()->route('product-versions.index')->with('success', 'Product Version created successfully.');
    }

    // Edit - show form
    public function edit(ProductVersion $productVersion)
    {
        return Inertia::render('ProductVersions/Edit', [
            'productVersion' => $productVersion
        ]);
    }

    // Update - save changes
    public function update(Request $request, ProductVersion $productVersion)
    {
        $data = $request->validate([
            'version_name' => 'required|string|max:255|unique:product_versions,version_name,' . $productVersion->id,
            'release_date' => 'required|date',
            'environment' => 'required|in:production,staging,development',
            'release_notes' => 'nullable|string',
            'build_id' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:255',
            'db_schema_version' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $productVersion->update($data);

        return redirect()->route('product-versions.index')->with('success', 'Product Version updated successfully.');
    }

    // Destroy - delete
    public function destroy(ProductVersion $productVersion)
    {
        $productVersion->delete();
        return redirect()->route('product-versions.index')->with('success', 'Product Version deleted successfully.');
    }
}