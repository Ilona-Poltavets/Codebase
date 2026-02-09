<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketSettingsController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        if (! $companyId && ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        return view('tickets.settings', [
            'statuses' => TicketStatus::where('company_id', $companyId)->orderBy('sort')->get(),
            'priorities' => TicketPriority::where('company_id', $companyId)->orderBy('sort')->get(),
            'categories' => TicketCategory::where('company_id', $companyId)->orderBy('sort')->get(),
            'types' => TicketType::where('company_id', $companyId)->orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        $companyId = $request->user()->company_id;
        if (! $companyId && ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        $model = $this->resolveModel($type);
        $model::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'slug' => $slug,
            'sort' => 100,
            'is_active' => true,
        ]);

        return redirect()->route('tickets.settings')->with('success', 'Saved.');
    }

    public function destroy(Request $request, string $type, int $id)
    {
        $companyId = $request->user()->company_id;
        $model = $this->resolveModel($type);
        $record = $model::where('company_id', $companyId)->findOrFail($id);
        $record->delete();

        return redirect()->route('tickets.settings')->with('success', 'Deleted.');
    }

    private function resolveModel(string $type)
    {
        return match ($type) {
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
            'type' => TicketType::class,
            default => abort(404),
        };
    }
}
