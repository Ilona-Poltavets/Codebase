<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\User;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::withCount('users')->orderBy('id')->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($companies);
        }

        return view('admin.companies', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.company-create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $company = Company::create($request->only('name', 'description'));

        if ($request->filled('user_id')) {
            User::whereKey($request->user_id)->update(['company_id' => $company->id]);
        }

        if ($request->wantsJson()) {
            return response()->json($company, 201);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        if (request()->wantsJson()) {
            return response()->json($company->load('users'));
        }

        return redirect()->route('admin.companies.edit', $company);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        $users = User::orderBy('name')->get();
        return view('admin.company-edit', compact('company', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->only('name', 'description'));

        if ($request->filled('user_id')) {
            User::whereKey($request->user_id)->update(['company_id' => $company->id]);
        }

        if ($request->wantsJson()) {
            return response()->json($company);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully.');
    }
}
