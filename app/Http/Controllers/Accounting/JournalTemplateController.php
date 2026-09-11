<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalTemplate;
use Illuminate\View\View;

class JournalTemplateController extends Controller
{
    public function index(): View
    {
        $journalTemplates = JournalTemplate::with('lines.account')
            ->orderBy('id')
            ->get();

        return view('accounting.journalTemplate', compact('journalTemplates'));
    }
}
