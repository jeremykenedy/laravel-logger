<?php

namespace jeremykenedy\LaravelLogger\Support;

use RuntimeException;
use XMLWriter;
use ZipArchive;

class ExcelExport
{
    public static function safeCell($value)
    {
        if (is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value)) {
            return "'".$value;
        }

        return $value;
    }

    public function download($activities)
    {
        abort_unless(class_exists(ZipArchive::class) && class_exists(XMLWriter::class), 503, 'Excel export requires the PHP zip and xmlwriter extensions.');
        $path = tempnam(sys_get_temp_dir(), 'logger_export_');
        if ($path === false) {
            throw new RuntimeException('Unable to create the export file.');
        }
        try {
            $zip = new ZipArchive;
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to open the export file.');
            }
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Activity Log" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($activities));
            $zip->close();
        } catch (\Throwable $exception) {
            unlink($path);
            throw $exception;
        }

        return response()->download($path, 'activity_log_'.now()->format('Y-m-d_H-i-s').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function worksheet($activities): string
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElementNs(null, 'worksheet', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xml->startElement('sheetData');
        $this->row($xml, ['ID', 'Description', 'Details', 'User Type', 'User ID', 'User Email', 'Route', 'IP Address', 'User Agent', 'Locale', 'Referer', 'Method Type', 'Created At', 'Updated At']);
        foreach ($activities as $activity) {
            $this->row($xml, [$activity->id, $activity->description, $activity->details, $activity->userType,
                $activity->userId, $activity->userDetails ? $activity->userDetails->email : 'N/A',
                $activity->route, $activity->ipAddress, $activity->userAgent, $activity->locale,
                $activity->referer, $activity->methodType, $activity->created_at, $activity->updated_at]);
        }
        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function row(XMLWriter $xml, array $values): void
    {
        $xml->startElement('row');
        foreach ($values as $value) {
            $xml->startElement('c');
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');
            $xml->writeAttribute('xml:space', 'preserve');
            $xml->text(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $value));
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
    }
}
