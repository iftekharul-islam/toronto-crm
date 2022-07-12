<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppraisalTypeCreateRequest;
use App\Repositories\AppraisalTypeRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppraisalTypeController extends BaseController
{
    protected AppraisalTypeRepository $repository;

    public function __construct(AppraisalTypeRepository $appraisal_type_repository)
    {
        parent::__construct();
        $this->repository = $appraisal_type_repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $company_id = auth()->user()->companies()->first()->id;
        $appraisal_types = $this->repository->allAppraisalTypes($company_id);
        return view('appraisal-type.index', compact('appraisal_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        return view('appraisal-type.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AppraisalTypeCreateRequest $request
     *
     * @return RedirectResponse
     */
    public function store(AppraisalTypeCreateRequest $request): RedirectResponse
    {
        $appraisal_type_data = [
            'company_id' => auth()->user()->companies()->first()->id,
            'form_type' => $request->form_type,
            'modified_form' => $request->modified_form,
            'condo_type' => $request->condo_type ? 1 : 0,
            'is_full_appraisal' => $request->is_full_appraisal ? 1 : 0,
        ];

        $this->repository->create($appraisal_type_data);

        return redirect()->route('appraisal-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Application|Factory|View
     */
    public function edit(int $id): View|Factory|Application
    {
        $appraisal_type = $this->repository->find($id);

        return view('appraisal-type.edit', compact('appraisal_type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AppraisalTypeCreateRequest $request
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'form_type'     => 'required|string|unique:appraisal_types,form_type,'. $id,
            'modified_form' => 'required|string|unique:appraisal_types,modified_form,'. $id,
        ]);

        $appraisal_type_data = [
            'form_type' => $request->form_type,
            'modified_form' => $request->modified_form,
            'condo_type' => $request->condo_type,
            'is_full_appraisal' => $request->is_full_appraisal ? 1 : 0,
        ];
        $this->repository->update(attributes: $appraisal_type_data, id: $id);

        return redirect()->route('appraisal-types.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json(['success' => $this->repository->delete($id)]);
    }
}
