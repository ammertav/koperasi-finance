<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Services\Accounting\InterOfficeAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OfficeController extends Controller
{
    public function __construct(private InterOfficeAccountService $interOfficeAccountService) {}

    public function index(): View
    {
        $offices = Office::with('parent')->orderBy('code')->get();

        return view('organization.office', compact('offices'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:offices,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:head_office,branch,sub_branch,cash_office',
            'parent_id' => 'nullable|exists:offices,id',
            'address' => 'nullable|string',
            'book_date' => 'required|date',
        ]);

        $structureError = $this->structureError($data['type'], $data['parent_id'] ?? null);

        if ($structureError) {
            return back()->withErrors(['msg' => $structureError])->withInput();
        }

        $data['parent_id'] = $data['type'] === 'head_office' ? null : $data['parent_id'];
        $data['is_active'] = $request->has('is_active');

        DB::beginTransaction();

        try {
            $office = Office::create($data);

            // RAK-01: pasangan akun RAK pusat–cabang dibentuk otomatis untuk kantor baru selain pusat.
            if ($office->type !== 'head_office') {
                $this->interOfficeAccountService->ensureAccountsFor($office);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['msg' => 'Kantor gagal ditambahkan: '.$e->getMessage()])->withInput();
        }

        return redirect(route('office'))->with('success', 'Kantor berhasil ditambahkan!');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $office = Office::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:head_office,branch,sub_branch,cash_office',
            'parent_id' => 'nullable|exists:offices,id',
            'address' => 'nullable|string',
        ]);

        if ((int) ($data['parent_id'] ?? 0) === $office->id) {
            return back()->withErrors(['msg' => 'Kantor induk tidak boleh kantor itu sendiri.'])->withInput();
        }

        $structureError = $this->structureError($data['type'], $data['parent_id'] ?? null, $office->id);

        if ($structureError) {
            return back()->withErrors(['msg' => $structureError])->withInput();
        }

        $data['parent_id'] = $data['type'] === 'head_office' ? null : $data['parent_id'];
        $data['is_active'] = $request->has('is_active');

        $office->update($data);

        return redirect(route('office'))->with('success', 'Kantor berhasil diperbarui!');
    }

    /**
     * Koperasi hanya punya satu kantor pusat, dan kantor lain wajib punya kantor induk (PRD bagian 4).
     */
    private function structureError(string $type, int|string|null $parentId, ?int $ignoreOfficeId = null): ?string
    {
        if ($type === 'head_office') {
            $headOfficeExists = Office::withoutGlobalScope(OfficeScope::class)
                ->where('type', 'head_office')
                ->when($ignoreOfficeId, fn ($query) => $query->where('id', '!=', $ignoreOfficeId))
                ->exists();

            return $headOfficeExists ? 'Kantor pusat sudah ada. Koperasi hanya memiliki satu kantor pusat.' : null;
        }

        return $parentId ? null : 'Kantor selain kantor pusat wajib memiliki kantor induk.';
    }
}
