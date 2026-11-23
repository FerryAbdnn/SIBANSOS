<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplatePenerimaExport implements
    FromArray,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents
{
    public function headings(): array
    {
        return [
            'NIK',
            'Nomor KK',
            'Nama Lengkap',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Nomor WhatsApp',
            'Alamat',
            'RW',
            'RT',
            'Penghasilan Bulanan',
            'Jumlah Tanggungan',
            'Kondisi Rumah',
            'Disabilitas',
            'Lansia',
            'Program Bantuan',
        ];
    }

    public function array(): array
    {
        return [
            [
                '3200000000000001',
                '3200000000000002',
                'Contoh Nama',
                '1990-01-15',
                'L',
                '081234567890',
                'Jl. Contoh No. 1',
                '01',
                '01',
                1500000,
                2,
                'permanen',
                0,
                0,
                'Program PKH',
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Paksa NIK dan Nomor KK menjadi teks.
                $sheet->setCellValueExplicit(
                    'A2',
                    '3200000000000001',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $sheet->setCellValueExplicit(
                    'B2',
                    '3200000000000002',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                // Nomor WhatsApp, RW, dan RT juga berupa teks.
                $sheet->setCellValueExplicit(
                    'F2',
                    '081234567890',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $sheet->setCellValueExplicit(
                    'H2',
                    '01',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $sheet->setCellValueExplicit(
                    'I2',
                    '01',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                // Nilai default Disabilitas dan Lansia.
                $sheet->setCellValueExplicit(
                    'M2',
                    '0',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );

                $sheet->setCellValueExplicit(
                    'N2',
                    '0',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            },
        ];
    }
}
