<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use App\Models\RhtCongeAgent;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;

class RhtCongeAgentImporter
{
    /**
     * Import INSERT statements for rhtcongeagent from storage/app/rhtcongeagent.sql
     * @param bool $truncate Empty the table before import
     * @return int number of executed INSERT statements
     * @throws \Exception
     */

    public function importExcel(string $path, bool $truncate = false): int
    {


        if (!file_exists($path)) {
            throw new \Exception("Excel file not found at: {$path}");
        }

        if ($truncate) {
            DB::table('rhtcongeagent')->truncate();
        }

            $import = new class implements ToModel, WithHeadingRow {
                public int $count = 0;

                private function parseDate(mixed $value): ?string
                {
                    if ($value === null || $value === '') {
                        return null;
                    }

                    // If it's already a DateTime
                    if ($value instanceof \DateTimeInterface) {
                        return $value->format('Y-m-d');
                    }

                    // If numeric, treat as Excel serialized date
                    if (is_numeric($value)) {
                        try {
                            $dt = PhpSpreadsheetDate::excelToDateTimeObject((float) $value);
                            return $dt->format('Y-m-d');
                        } catch (\Throwable $e) {
                            // fall through to string parsing
                        }
                    }

                    // Normalize separators
                    $s = trim((string) $value);
                    $s = str_replace(['\\', '.'], ['/', '/'], $s);

                    $formats = ['d/m/y', 'd/m/Y', 'd-m-y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'm/d/Y', 'm/d/y'];
                    foreach ($formats as $fmt) {
                        $dt = \DateTime::createFromFormat($fmt, $s);
                        if ($dt !== false) {
                            return $dt->format('Y-m-d');
                        }
                    }

                    // Try strtotime after replacing slashes with dashes
                    $ts = strtotime(str_replace('/', '-', $s));
                    if ($ts !== false) {
                        return date('Y-m-d', $ts);
                    }

                    return null;
                }

                public function model(array $row)
                {
                    $this->count++;

                    $data = [
                        'CDOS' => $row['cdos'] ?? null,
                        'NMAT' => $row['nmat'] ?? null,
                        'DDCG' => $this->parseDate($row['ddcg'] ?? null),
                        'DFCG' => $this->parseDate($row['dfcg'] ?? null),
                        'NBJA' => $row['nbja'] ?? null,
                        'NBJC' => $row['nbjc'] ?? null,
                        'CMCG' => $row['cmcg'] ?? null,
                        'MTCG' => $row['mtcg'] ?? null,
                        'TYPA' => $row['typa'] ?? null,
                        'CCLO' => $row['cclo'] ?? null,
                        'DCLO' => $this->parseDate($row['dclo'] ?? null),
                        'DEFF' => $this->parseDate($row['deff'] ?? null),
                        'CUTICRE' => $row['cuticre'] ?? null,
                        'DATECRE' => $this->parseDate($row['datecre'] ?? null),
                        'CUTIMOD' => $row['cutimod'] ?? null,
                        'DATEMOD' => $this->parseDate($row['datemod'] ?? null),
                        'TYPCG' => $row['typcg'] ?? null,
                        'REF' => $row['ref'] ?? null,
                    ];

                    return new RhtCongeAgent($data);
                }
            };

        try {
            Excel::import($import, $path);
            return $import->count;
        } catch (\Exception $e) {
            // Bubble up exception; do not attempt DB rollback here because
            // nested transactions / savepoints are managed by the DB driver
            // and Maatwebsite\Excel internals. Let the caller handle errors.
            throw $e;
        }
    }
}
