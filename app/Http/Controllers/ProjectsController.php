<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Http\Requests\StoreProjectsRequest;
use App\Http\Requests\UpdateProjectsRequest;
use App\Models\Company;

class ProjectsController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function sectionResponse(Projects $project, string $section)
    {
        $this->authorizeProjectAccess($project);

        $project->load('company');

        $statusCounts = [
            'new' => 6,
            'in_progress' => 3,
            'done' => 12,
        ];

        $tickets = [
            ['title' => 'Setup CI pipeline', 'status' => 'in_progress', 'priority' => 'high', 'category' => 'general', 'type' => 'feature', 'assignee' => 'Alex'],
            ['title' => 'Fix login validation', 'status' => 'new', 'priority' => 'medium', 'category' => 'api', 'type' => 'bug', 'assignee' => 'Maria'],
            ['title' => 'Refactor auth middleware', 'status' => 'done', 'priority' => 'low', 'category' => 'refactoring', 'type' => 'feature', 'assignee' => 'Ivan'],
        ];
        $repositories = [
            'api-service',
            'frontend-app',
            'infra-scripts',
        ];

        return view('projects.section', [
            'project' => $project,
            'section' => $section,
            'statusCounts' => $statusCounts,
            'tickets' => $tickets,
            'repositories' => $repositories,
        ]);
    }

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
        if (request()->user()->hasRole('owner') || request()->user()->hasRole('manager')) {
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
        if ($request->user()->hasRole('owner') || $request->user()->hasRole('manager')) {
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
        $this->authorizeProjectAccess($project);
        if (request()->wantsJson()) {
            return response()->json($project->load('company'));
        }

        return redirect()->route('admin.projects.overview', $project);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $companies = Company::orderBy('name')->get();
        if (request()->user()->hasRole('owner') || request()->user()->hasRole('manager')) {
            $companies = $companies->where('id', request()->user()->company_id);
        }
        return view('admin.project-edit', compact('project', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectsRequest $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);
        $data = $request->validated();
        if ($request->user()->hasRole('owner') || $request->user()->hasRole('manager')) {
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
        $this->authorizeProjectAccess($project);
        $project->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully.');
    }

    public function overview(Projects $project)
    {
        return $this->sectionResponse($project, 'overview');
    }

    public function tickets(Projects $project)
    {
        return $this->sectionResponse($project, 'tickets');
    }

    public function files(Projects $project)
    {
        return $this->sectionResponse($project, 'files');
    }

    public function time(Projects $project)
    {
        return $this->sectionResponse($project, 'time');
    }
}
