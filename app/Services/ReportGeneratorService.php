<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class ReportGeneratorService
{
    private const OUTPUT_DIR = 'generated_reports';

    public function generateDraft(Report $report): string
    {
        $templateProcessor = $this->makeProcessor($report);

        $this->fillTables($templateProcessor, $report);
        $this->fillTextFields($templateProcessor, $report);

        if (in_array('logo', $templateProcessor->getVariables(), true)) {
            $templateProcessor->setValue('logo', '');
        }

        $relativePath = self::OUTPUT_DIR."/{$report->id}/brouillon-".now()->format('Ymd-His').'.docx';
        $this->saveProcessor($templateProcessor, $relativePath);

        $report->update(['draft_export_path' => $relativePath]);

        return $relativePath;
    }

    public function generateValidated(Report $report, string $logoAbsolutePath): string
    {
        $templateProcessor = $this->makeProcessor($report);

        $this->fillTables($templateProcessor, $report);
        $this->fillTextFields($templateProcessor, $report);

        if (in_array('logo', $templateProcessor->getVariables(), true)) {
            $templateProcessor->setImageValue('logo', [
                'path' => $logoAbsolutePath,
                'width' => 140,
                'height' => 70,
                'ratio' => true,
            ]);
        }

        $relativePath = self::OUTPUT_DIR."/{$report->id}/final-".now()->format('Ymd-His').'.docx';
        $this->saveProcessor($templateProcessor, $relativePath);

        $report->update(['validated_path' => $relativePath]);

        return $relativePath;
    }

    private function makeProcessor(Report $report): TemplateProcessor
    {
        $templatePath = Storage::path($report->type->template_path);

        return new TemplateProcessor($templatePath);
    }

    private function fillTextFields(TemplateProcessor $templateProcessor, Report $report): void
    {
        foreach ($report->type->fields as $field) {
            // Les champs "tableau" sont gérés à part par fillTables() —
            // leur valeur est un tableau de lignes, pas un simple texte.
            if ($field['type'] === 'table') {
                continue;
            }

            $key = $field['key'];
            $value = $report->data[$key] ?? '';

            $templateProcessor->setValue($key, $this->formatValue($value, $field['type']));
        }
    }

    /**
     * Génère les vrais tableaux Word à lignes répétées, à partir des
     * données saisies dans l'interface façon "mini-Excel".
     */
    private function fillTables(TemplateProcessor $templateProcessor, Report $report): void
    {
        foreach ($report->type->fields as $field) {
            if ($field['type'] !== 'table') {
                continue;
            }

            $rows = $report->data[$field['key']] ?? [];

            // On retire les lignes totalement vides (ex: une ligne
            // ajoutée par erreur puis jamais remplie).
            $rows = array_values(array_filter(
                $rows,
                fn ($r) => is_array($r) && count(array_filter($r, fn ($v) => $v !== '' && $v !== null)) > 0
            ));

            $count = max(count($rows), 1); // cloneRow() exige au moins 1

            // On se sert du nom technique de la PREMIÈRE colonne pour que
            // PHPWord repère la bonne ligne dans le tableau Word — peu
            // importe laquelle, du moment qu'elle existe dans le template.
            $anchorKey = $field['columns'][0]['key'] ?? null;
            if (! $anchorKey) {
                continue;
            }

            try {
                $templateProcessor->cloneRow($anchorKey, $count);
            } catch (\Throwable $e) {
                // On affiche maintenant le VRAI message technique renvoyé par PHPWord
                // (getMessage()), au lieu de notre supposition générique — indispensable
                // pour diagnostiquer précisément plutôt que deviner.
                throw new \RuntimeException(
                    "Impossible de générer le tableau « {$field['label']} » (repère \${$anchorKey}). "
                    ."Message technique original : ".$e->getMessage()
                );
            }

            foreach ($rows as $i => $row) {
                $n = $i + 1; // PHPWord numérote les clones à partir de 1
                foreach ($field['columns'] as $col) {
                    $value = $row[$col['key']] ?? '';
                    if ($col['type'] === 'number' && $value !== '') {
                        $value = number_format((float) $value, 0, ',', ' ');
                    }
                    $templateProcessor->setValue("{$col['key']}#{$n}", (string) $value);
                }
            }

            if (($field['show_total'] ?? false) && isset($field['computed'])) {
                $total = array_sum(array_map(
                    fn ($r) => (float) ($r[$field['computed']['target']] ?? 0),
                    $rows
                ));

                $templateProcessor->setValue(
                    $field['key'].'_total',
                    number_format($total, 0, ',', ' ')
                );
            }
        }
    }

    private function formatValue(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($type) {
            'date' => \Illuminate\Support\Carbon::parse($value)->format('d/m/Y'),
            'number' => number_format((float) $value, 0, ',', ' '),
            default => (string) $value,
        };
    }

    private function saveProcessor(TemplateProcessor $templateProcessor, string $relativePath): void
    {
        Storage::makeDirectory(dirname($relativePath));

        $templateProcessor->saveAs(Storage::path($relativePath));
    }
}