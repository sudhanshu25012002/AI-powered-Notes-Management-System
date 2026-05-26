<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\View\View;

class NoteWebController extends Controller
{
    public function index(): View
    {
        $notes = Note::latest()->paginate(10);
        return view('notes.index', compact('notes'));
    }

    public function create(): View
    {
        return view('notes.create');
    }

    public function show(int $id): View
    {
        $note = Note::findOrFail($id);
        return view('notes.show', compact('note'));
    }

    public function edit(int $id): View
    {
        $note = Note::findOrFail($id);
        return view('notes.edit', compact('note'));
    }
}
