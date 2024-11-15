<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Technology;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $projects = Project::all();
        $projects = Project::paginate(9);
        // dump($projects);
        return view("admin.projects.index", compact("projects"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = Type::all();
        $technologies = Technology::all();
        $project = new Project();
        return view("admin.projects.create", compact("project", "types", "technologies"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        // dd($request->file('img_url'));
        $data = $request->validated();

        if ($request->hasFile('img_url')) {
            $filePath = $request->file('img_url')->store('img/projects', 'public');
            $data['img_url'] = $filePath;
        }


        $project = Project::create($data);

        if (isset($data["technologies"])) {
            $project->technologies()->sync($data["technologies"]);
        } else {
            $project->technologies()->detach();
        }

        return redirect()->route("admin.projects.show", $project);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view("admin.projects.show", compact("project"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $types = Type::all();
        $technologies = Technology::all();

        return view('admin.projects.edit', compact("project", "types", "technologies"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        if ($request->hasFile("img_url")) {
            if ($project->img_url) {
                Storage::disk("public")->delete($project->img_url);
            }
            $filePath = Storage::disk("public")->put("img/projects/", $request->img_url);
            $data["img_url"] = $filePath;
        }

        $project->update($data);

        if (isset($data["technologies"])) {
            $project->technologies()->sync($data["technologies"]);
        } else {
            $project->technologies()->detach();
        }
        return redirect()->route("admin.projects.show", $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);

        $project->delete();
        return redirect()->route("admin.projects.index");
    }

    public function deletedIndex()
    {
        $projects = Project::onlyTrashed()->get();
        return view("admin.projects.deleted-index", compact("projects"));
    }

    public function restore(string $id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        $project->restore();
        return redirect()->route("admin.projects.index");
    }

    public function permanentDelete(string $id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->forceDelete();
        return redirect()->route("admin.projects.deleted.index");
    }

    public function __construct()
    {
        $this->middleware("auth")->except(["index"]);
    }
}
