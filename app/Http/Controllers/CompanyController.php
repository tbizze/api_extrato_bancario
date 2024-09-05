<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $companies = Company::all();

        return view('companies.index', compact('companies'));
    }

    /**
     * Display the specified resource.
     */
    public function create(): View
    {

        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => 'required|string']);
        Company::create($validated);

        return redirect()->route('companies.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): View
    {
        return view('companies.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('companies.index');
    }
}
