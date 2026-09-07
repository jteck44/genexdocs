<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class TemplateAnalyzerService
{
    private const STORAGE_DIR = 'report_templates';

    public function analyze(UploadedFile $file): array
    {
        $templateProcessor = new TemplateProcessor($file->getRealPath());

        $variables = $templateProcessor->getVariables();
        $variables = array_values(array_unique($variables));

        return [
            'variables' => $variables,
            'has_logo' => in_array('logo', $variables, true),
        ];
    }

    public function guessFieldType(string $key): string
    {
        $key = strtolower($key);

        if (str_contains($key, 'date')) {
            return 'date';
        }

        if (str_contains($key, 'montant')
            || str_contains($key, 'prix')
            || str_contains($key, 'total')
            || str_contains($key, 'quantite')
            || str_contains($key, 'qte')
            || str_contains($key, 'nombre')) {
            return 'number';
        }

        if (str_contains($key, 'description')
            || str_contains($key, 'observation')
            || str_contains($key, 'circonstance')
            || str_contains($key, 'commentaire')
            || str_contains($key, 'motif')
            || str_contains($key, 'constat')) {
            return 'textarea';
        }

        return 'text';
    }

    /**
     * NOUVELLE VERSION — insertion chirurgicale du ${logo} directement
     * dans le XML de l'en-tête, sans jamais recharger/réécrire tout le
     * document (contrairement à l'ancienne méthode, qui abîmait les
     * mises en page complexes).
     *
     * @return string 'present', 'added', ou 'no_header_available'
     */
    public function ensureLogoPlaceholder(string $absoluteFilePath): string
    {
        $zip = new ZipArchive();

        if ($zip->open($absoluteFilePath) !== true) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier .docx (archive corrompue ?).");
        }

        $headerNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^word/header\d+\.xml$#', (string) $name)) {
                $headerNames[] = $name;
            }
        }

        foreach ($headerNames as $name) {
            $xml = $zip->getFromName($name);
            if ($xml !== false && str_contains($xml, '${logo}')) {
                $zip->close();

                return 'present';
            }
        }

        if (! empty($headerNames)) {
            $targetHeader = $headerNames[0];
            $xml = $zip->getFromName($targetHeader);

            $insertion = '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
                       .'<w:r><w:t>${logo}</w:t></w:r></w:p>';

            $newXml = preg_replace('/(<w:hdr[^>]*>)/', '$1'.$insertion, $xml, 1);

            $zip->deleteName($targetHeader);
            $zip->addFromString($targetHeader, $newXml);
            $zip->close();

            return 'added';
        }

        $zip->close();

        return 'no_header_available';
    }

    /**
     * @return array{path: string, logo_status: string}
     */
    public function storeTemplate(UploadedFile $file, string $slug, ?string $existingRelativePath = null): array
    {
        if ($existingRelativePath && Storage::exists($existingRelativePath)) {
            $archivePath = self::STORAGE_DIR.'/archive/'.$slug.'-'.now()->format('Ymd-His').'.docx';
            Storage::move($existingRelativePath, $archivePath);
        }

        $relativePath = self::STORAGE_DIR.'/'.$slug.'.docx';

        $file->storeAs(self::STORAGE_DIR, $slug.'.docx');

        $absolutePath = Storage::path($relativePath);
        $logoStatus = $this->ensureLogoPlaceholder($absolutePath);

        return [
            'path' => $relativePath,
            'logo_status' => $logoStatus,
        ];
    }
}