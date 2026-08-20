<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\DpaDocumentTemplate;
use App\Support\DpaMonitoring;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        $templates = DpaDocumentTemplate::query()
            ->orderBy('tahap')
            ->orderBy('kode')
            ->get()
            ->groupBy('tahap');

        $tahapLabels = collect(DpaMonitoring::TAHAP)
            ->mapWithKeys(fn (string $tahap) => [$tahap => DpaMonitoring::tahapLabel($tahap)])
            ->all();

        return view('admin.dpa.document_templates.index', [
            'templates' => $templates,
            'tahapLabels' => $tahapLabels,
        ]);
    }

    public function update(Request $request, DpaDocumentTemplate $documentTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'template_file' => ['required', 'file', 'max:10240'],
        ]);

        $this->deletePublicFile($documentTemplate->template_path);

        $documentTemplate->update([
            'template_path' => $this->storeTemplateFile($request->file('template_file'), $documentTemplate->kode),
        ]);

        return redirect()
            ->route('admin.dpa.document-templates.index')
            ->with('success', 'Template '.$documentTemplate->nama.' berhasil diperbarui.');
    }

    private function storeTemplateFile(UploadedFile $file, string $kode): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $kode.($extension !== '' ? '.'.$extension : '');
        $path = $file->storeAs('dpa-templates', $filename, 'public');

        return 'storage/'.$path;
    }

    private function deletePublicFile(?string $filePath): void
    {
        if (blank($filePath) || ! str_starts_with($filePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($filePath, strlen('storage/')));
    }
}
