<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\Template;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function __construct(protected readonly TemplateService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Auth::user()->templates;
        $defaultTemplateId = Auth::user()->default_template_id;

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
            'defaultTemplateId' => $defaultTemplateId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Templates/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTemplateRequest $request)
    {
        $isDefault = $request->input('is_default', false);

        $template = $this->service->storeTemplate($request->merge([
            'user_id' => Auth::id(),
        ])->all(), $isDefault);

        return redirect()->route('templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $template = Auth::user()->templates()->findOrFail($id);

        return Inertia::render('Templates/Show', [
            'template' => $template,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $template = Auth::user()->templates()->findOrFail($id);

        return Inertia::render('Templates/Edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTemplateRequest $request, string $id)
    {
        $template = Auth::user()->templates()->findOrFail($id);
        $isDefault = $request->input('is_default', false);

        $this->service->updateTemplate($template, $request->merge([
            'user_id' => Auth::id(),
        ])->all(), $isDefault);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $template = Auth::user()->templates()->findOrFail($id);

        if ($template->is_default) {
            Auth::user()->update(['default_template_id' => null]);
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Set the specified template as the default.
     */
    public function setDefault(string $id)
    {
        $template = Auth::user()->templates()->findOrFail($id);

        $this->service->setDefaultTemplate($template);

        return redirect()->route('templates.index')
            ->with('success', 'Default template updated successfully.');
    }
}
