<?php

namespace App\Http\Controllers;

use App\Models\ProjectRepository;
use App\Models\Projects;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WikiPageController extends Controller
{
    private function authorizeProjectAccess(Projects $project): void
    {
        if (! request()->user()->hasRole('admin') && $project->company_id !== request()->user()->company_id) {
            abort(403);
        }
    }

    private function repositories(Projects $project)
    {
        if (! Schema::hasTable('project_repositories')) {
            return collect();
        }

        return ProjectRepository::where('project_id', $project->id)
            ->orderBy('name')
            ->get();
    }

    private function uniqueSlug(Projects $project, string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        if ($slug === '') {
            $slug = 'wiki-page';
        }

        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            $query = WikiPage::where('project_id', $project->id)->where('slug', $slug);
            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
            if (! $query->exists()) {
                return $slug;
            }
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }
    }

    private function createSnapshot(WikiPage $page, ?int $editorId): WikiPageVersion
    {
        $nextVersion = (int) ($page->versions()->max('version') ?? 0) + 1;

        return WikiPageVersion::create([
            'wiki_page_id' => $page->id,
            'version' => $nextVersion,
            'title' => $page->title,
            'content' => $page->content,
            'edited_by' => $editorId,
        ]);
    }

    public function index(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $pages = WikiPage::where('project_id', $project->id)
            ->withCount('versions')
            ->orderBy('title')
            ->get();

        $selectedPage = null;
        $selectedVersion = null;

        $pageId = $request->query('page_id');
        if ($pageId) {
            $selectedPage = WikiPage::where('project_id', $project->id)
                ->with(['creator', 'editor'])
                ->findOrFail($pageId);
        } else {
            $selectedPage = WikiPage::where('project_id', $project->id)
                ->with(['creator', 'editor'])
                ->orderBy('title')
                ->first();
        }

        if ($selectedPage) {
            $versionId = $request->query('version_id');
            if ($versionId) {
                $selectedVersion = WikiPageVersion::where('wiki_page_id', $selectedPage->id)
                    ->with('editor')
                    ->findOrFail($versionId);
            }
            $selectedPage->load(['versions.editor']);
        }

        $sourceTitle = $selectedVersion?->title ?? $selectedPage?->title ?? '';
        $sourceContent = $selectedVersion?->content ?? $selectedPage?->content ?? '';
        $renderedContent = $sourceContent === '' ? '' : Str::markdown($sourceContent);

        return view('projects.wiki', [
            'project' => $project->load('company'),
            'section' => 'wiki',
            'repositories' => $this->repositories($project),
            'pages' => $pages,
            'selectedPage' => $selectedPage,
            'selectedVersion' => $selectedVersion,
            'sourceTitle' => $sourceTitle,
            'sourceContent' => $sourceContent,
            'renderedContent' => $renderedContent,
        ]);
    }

    public function store(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $page = WikiPage::create([
            'project_id' => $project->id,
            'created_by' => $userId,
            'updated_by' => $userId,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($project, $data['title']),
            'content' => $data['content'] ?? '',
        ]);
        $this->createSnapshot($page, $userId);

        ActivityLogger::log($project->id, $userId, 'wiki.page_created', [
            'page_id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
        ]);

        return redirect()
            ->route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $page->id])
            ->with('success', 'Wiki page created.');
    }

    public function update(Request $request, Projects $project, WikiPage $wikiPage)
    {
        $this->authorizeProjectAccess($project);
        if ($wikiPage->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $newTitle = $data['title'];
        $newContent = $data['content'] ?? '';
        $hasChanges = ($wikiPage->title !== $newTitle) || ((string) $wikiPage->content !== $newContent);

        if (! $hasChanges) {
            return redirect()
                ->route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $wikiPage->id])
                ->with('success', 'No changes to save.');
        }

        $wikiPage->update([
            'title' => $newTitle,
            'slug' => $this->uniqueSlug($project, $newTitle, $wikiPage->id),
            'content' => $newContent,
            'updated_by' => $request->user()->id,
        ]);
        $version = $this->createSnapshot($wikiPage, $request->user()->id);

        ActivityLogger::log($project->id, $request->user()->id, 'wiki.page_updated', [
            'page_id' => $wikiPage->id,
            'title' => $wikiPage->title,
            'version' => $version->version,
        ]);

        return redirect()
            ->route('admin.projects.wiki', ['project' => $project->id, 'page_id' => $wikiPage->id])
            ->with('success', 'Wiki page updated.');
    }

    public function destroy(Projects $project, WikiPage $wikiPage)
    {
        $this->authorizeProjectAccess($project);
        if ($wikiPage->project_id !== $project->id) {
            abort(404);
        }

        ActivityLogger::log($project->id, request()->user()->id, 'wiki.page_deleted', [
            'page_id' => $wikiPage->id,
            'title' => $wikiPage->title,
            'slug' => $wikiPage->slug,
        ]);

        $wikiPage->delete();

        return redirect()
            ->route('admin.projects.wiki', ['project' => $project->id])
            ->with('success', 'Wiki page deleted.');
    }

    public function indexApi(Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $pages = WikiPage::where('project_id', $project->id)
            ->withCount('versions')
            ->orderBy('title')
            ->get();

        return response()->json($pages);
    }

    public function showApi(Projects $project, WikiPage $wikiPage)
    {
        $this->authorizeProjectAccess($project);
        if ($wikiPage->project_id !== $project->id) {
            abort(404);
        }

        $wikiPage->load(['versions.editor']);

        return response()->json([
            'id' => $wikiPage->id,
            'project_id' => $wikiPage->project_id,
            'title' => $wikiPage->title,
            'slug' => $wikiPage->slug,
            'content' => $wikiPage->content,
            'rendered_content' => Str::markdown($wikiPage->content ?? ''),
            'versions' => $wikiPage->versions,
            'created_at' => $wikiPage->created_at,
            'updated_at' => $wikiPage->updated_at,
        ]);
    }

    public function storeApi(Request $request, Projects $project)
    {
        $this->authorizeProjectAccess($project);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $page = WikiPage::create([
            'project_id' => $project->id,
            'created_by' => $userId,
            'updated_by' => $userId,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($project, $data['title']),
            'content' => $data['content'] ?? '',
        ]);
        $this->createSnapshot($page, $userId);

        ActivityLogger::log($project->id, $userId, 'wiki.page_created', [
            'page_id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'via_api' => true,
        ]);

        return response()->json($page->fresh(['versions']), 201);
    }

    public function updateApi(Request $request, Projects $project, WikiPage $wikiPage)
    {
        $this->authorizeProjectAccess($project);
        if ($wikiPage->project_id !== $project->id) {
            abort(404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $wikiPage->update([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($project, $data['title'], $wikiPage->id),
            'content' => $data['content'] ?? '',
            'updated_by' => $request->user()->id,
        ]);
        $version = $this->createSnapshot($wikiPage, $request->user()->id);

        ActivityLogger::log($project->id, $request->user()->id, 'wiki.page_updated', [
            'page_id' => $wikiPage->id,
            'title' => $wikiPage->title,
            'version' => $version->version,
            'via_api' => true,
        ]);

        return response()->json($wikiPage->fresh(['versions']));
    }

    public function destroyApi(Projects $project, WikiPage $wikiPage)
    {
        $this->authorizeProjectAccess($project);
        if ($wikiPage->project_id !== $project->id) {
            abort(404);
        }

        ActivityLogger::log($project->id, request()->user()->id, 'wiki.page_deleted', [
            'page_id' => $wikiPage->id,
            'title' => $wikiPage->title,
            'slug' => $wikiPage->slug,
            'via_api' => true,
        ]);

        $wikiPage->delete();

        return response()->json(null, 204);
    }
}
