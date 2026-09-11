<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mockups\LoanMockup;
use App\Mockups\MemberMockup;
use App\Mockups\SavingsMockup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Keanggotaan versi prototype tampilan: data dari MemberMockup, perubahan hanya disimpan di session demo.
 */
class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $allMembers = MemberMockup::forUser($user);
        $status = $request->query('status');

        $members = array_key_exists((string) $status, MemberMockup::STATUSES)
            ? array_filter($allMembers, fn (array $member) => $member['status'] === $status)
            : $allMembers;

        $statusCounts = array_count_values(array_column($allMembers, 'status'));
        $membersWithLoan = count(array_filter($allMembers, fn (array $member) => LoanMockup::activeLoans($member) !== []));

        $summary = [
            'active' => $statusCounts['active'] ?? 0,
            'pending_approval' => $statusCounts['pending_approval'] ?? 0,
            'approved' => $statusCounts['approved'] ?? 0,
            'with_loan' => $membersWithLoan,
        ];

        $statuses = MemberMockup::STATUSES;
        $showOffice = $user->canAccessAllOffices();

        return view('member.member', compact('members', 'summary', 'statuses', 'status', 'showOffice'));
    }

    public function create(): View
    {
        $office = Auth::user()->office;
        $occupations = MemberMockup::OCCUPATIONS;
        $heirRelationships = MemberMockup::HEIR_RELATIONSHIPS;
        $genders = MemberMockup::GENDERS;
        $consentText = config('demo.data_consent_text');

        return view('member.memberCreate', compact('office', 'occupations', 'heirRelationships', 'genders', 'consentText'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['monthly_income' => str_replace('.', '', (string) $request->input('monthly_income'))]);

        $data = $request->validate([
            'nik' => 'required|digits:16',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date|before:-17 years',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'occupation' => 'required|string|max:100',
            'monthly_income' => 'required|integer|min:0',
            'heir_name' => 'required|string|max:255',
            'heir_relationship' => 'required|string|max:50',
            'heir_phone' => 'nullable|string|max:20',
            'ktp_photo' => 'required|image|max:2048',
            'data_consent' => 'accepted',
        ]);

        $check = MemberMockup::nikCheck($data['nik']);

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']])->withInput();
        }

        // Prototype tampilan: foto KTP hanya divalidasi, tidak disimpan.
        $candidate = MemberMockup::addCandidate([...$data, 'has_ktp_photo' => true], auth()->user());

        return redirect(route('detailMember', $candidate['id']))
            ->with('success', 'Calon anggota berhasil didaftarkan dan menunggu persetujuan kepala cabang!');
    }

    public function checkNik(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nik' => 'required|digits:16',
        ]);

        return response()->json(MemberMockup::nikCheck($data['nik']));
    }

    public function show(string $id): View
    {
        $user = Auth::user();
        $member = MemberMockup::find($user, (int) $id) ?? abort(404);

        $savingsAccounts = SavingsMockup::forMember($member);
        $loans = LoanMockup::forMember($member);
        $activeLoans = LoanMockup::activeLoans($member);
        $collaterals = array_values(array_filter($loans, fn (array $loan) => $loan['collateral'] !== null));
        $timeline = MemberMockup::timeline($member, $loans);

        $summary = [
            'total_savings' => array_sum(array_column($savingsAccounts, 'balance')),
            'outstanding_principal' => array_sum(array_column($activeLoans, 'outstanding_principal')),
            'active_loan_count' => count($activeLoans),
            'collectibility' => LoanMockup::worstCollectibility($activeLoans),
        ];

        $canApprove = $member['status'] === 'pending_approval' && $user->hasAccess('member', 'approve');
        $principalSavings = config('demo.savings.principal');

        return view('member.memberShow', compact(
            'member', 'savingsAccounts', 'loans', 'collaterals', 'timeline', 'summary', 'canApprove', 'principalSavings'
        ));
    }

    public function approve(string $id): RedirectResponse
    {
        $user = auth()->user();
        $member = MemberMockup::find($user, (int) $id) ?? abort(404);

        if ($member['status'] !== 'pending_approval') {
            return back()->withErrors(['msg' => 'Hanya pendaftaran yang menunggu persetujuan yang dapat disetujui.']);
        }

        if ($member['registered_by_id'] === $user->id) {
            return back()->withErrors(['msg' => 'Pendaftar tidak boleh menyetujui pendaftarannya sendiri.']);
        }

        MemberMockup::approve($member, $user);

        return redirect(route('detailMember', $member['id']))
            ->with('success', 'Pendaftaran disetujui! Calon anggota dapat menyetor simpanan pokok di teller.');
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $user = auth()->user();
        $member = MemberMockup::find($user, (int) $id) ?? abort(404);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:200',
        ]);

        if ($member['status'] !== 'pending_approval') {
            return back()->withErrors(['msg' => 'Hanya pendaftaran yang menunggu persetujuan yang dapat ditolak.']);
        }

        if ($member['registered_by_id'] === $user->id) {
            return back()->withErrors(['msg' => 'Pendaftar tidak boleh menolak pendaftarannya sendiri.']);
        }

        MemberMockup::reject($member, $user, $data['rejection_reason']);

        return redirect(route('detailMember', $member['id']))->with('success', 'Pendaftaran ditolak!');
    }
}
