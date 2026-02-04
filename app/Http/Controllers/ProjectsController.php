<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Http\Requests\StoreProjectsRequest;
use App\Http\Requests\UpdateProjectsRequest;
use App\Models\Company;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Projects::with('company')->orderBy('id');
        if (! request()->user()->hasRole('admin')) {
            $query->where('company_id', request()->user()->company_id);
        }
        $projects = $query->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($projects);
        }

        return view('admin.projects', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        if (! request()->user()->hasRole('admin')) {
            $companies = $companies->where('id', request()->user()->company_id);
        }
        return view('admin.project-create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectsRequest $request)
    {
        $data = $request->validated();
        if (! $request->user()->hasRole('admin')) {
            $data['company_id'] = $request->user()->company_id;
        }
        $project = Projects::create($data);

        if ($request->wantsJson()) {
            return response()->json($project, 201);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $project)
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
        if (request()->wantsJson()) {
            return response()->json($project->load('company'));
        }

        return redirect()->route('admin.projects.edit', $project);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $project)
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
        $companies = Company::orderBy('name')->get();
        if (! request()->user()->hasRole('admin')) {
            $companies = $companies->where('id', request()->user()->company_id);
        }
        return view('admin.project-edit', compact('project', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectsRequest $request, Projects $project)
    {
        if (! $request->user()->hasRole('admin') && $project->company_id !== $request->user()->company_id) {
            abort(403);
        }
        $data = $request->validated();
        if (! $request->user()->hasRole('admin')) {
            $data['company_id'] = $request->user()->company_id;
        }
        $project->update($data);

        if ($request->wantsJson()) {
            return response()->json($project);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $project)
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
        $project->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }
}
