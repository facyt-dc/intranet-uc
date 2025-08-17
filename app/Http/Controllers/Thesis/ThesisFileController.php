<?php

namespace App\Http\Controllers\Thesis;

use App\Models\Thesis;
use App\Models\ThesisFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ThesisFileController extends Controller
{
    public function store(Request $request, Thesis $thesis)
    {
        $request->validate([
            // 'files' es el nombre del input en el formulario. 'files.*' valida cada archivo en el array.
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,doc,docx,zip|max:20480', // 20MB max
        ]);

        foreach ($request->file('files') as $file) {
            // Guardar el archivo en el disco 'public' dentro de una carpeta 'thesis_files'
            $path = $file->store('thesis_files', 'public');

            // Usar la relación para crear el registro en la base de datos.
            // Laravel automáticamente llenará el 'thesis_id'.
            $thesis->files()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,

            ]);
        }

        return back()->with('success', 'Archivos subidos con éxito.');
    }

    public function download(ThesisFile $thesisFile)
    {

        // Verificamos que el archivo realmente existe en el disco para evitar errores
        if (!Storage::disk('public')->exists($thesisFile->path)) {
            abort(404, 'El archivo no fue encontrado.');
        }
        // Construye la ruta física completa al archivo.
        $pathToFile = storage_path('app/public/' . $thesisFile->path);

        // El segundo argumento es el nombre que tendrá el archivo al descargarse.
        $fileName = $thesisFile->original_name;
        
        // Devolvemos la respuesta de descarga.
        return response()->download($pathToFile, $fileName);
    }
}