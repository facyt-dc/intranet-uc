<?php

namespace Modules\Thesis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Thesis\Models\Thesis;
use Modules\Thesis\Models\ThesisFile;

class ThesisFileController extends Controller
{
    public function store(Request $request, Thesis $thesis)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,doc,docx,zip|max:20480',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('thesis_files', 'public');

            $thesis->files()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
            ]);
        }

        return back()->with('success', 'Archivos subidos con éxito.');
    }

    public function download(ThesisFile $thesisFile)
    {
        if (!Storage::disk('public')->exists($thesisFile->path)) {
            abort(404, 'El archivo no fue encontrado.');
        }

        $pathToFile = storage_path('app/public/' . $thesisFile->path);
        $fileName = $thesisFile->original_name;

        return response()->download($pathToFile, $fileName);
    }
}