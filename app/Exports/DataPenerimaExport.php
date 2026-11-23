<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataPenerimaExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithStyles,
    WithCustomValueBinder
{
    private $penerima;

    public function __construct($penerima)
    {
        $this->penerima = $penerima;
    }

    public function collection()
    {
        return $this->penerima;
    }

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
            'Status Penerima',
            'KTP',
            'KK',
            'DTKS/P3KE',
            'Foto Rumah Depan',
            'Foto Rumah Dalam',
            'SKTM',
            'Total Dokumen',
            'Kelengkapan Dokumen',
        ];
    }

    public function map($p): array
    {
        $dokumen = $p->dokumen;

        $ada = function ($jenis, $label = null) use ($dokumen) {
            return $dokumen->contains(function ($item) use ($jenis, $label) {
                if ($item->jenis_dokumen !== $jenis) {
                    return false;
                }

                if ($label !== null) {
                    return str_starts_with(
                        $item->nama_file,
                        $label . ' - '
                    );
                }

                return true;
            });
        };

        $statusDokumen = [
            $ada('ktp') ? 'Ada' : 'Belum ada',
            $ada('kk') ? 'Ada' : 'Belum ada',
            $ada('lainnya', 'Bukti DTKS/P3KE') ? 'Ada' : 'Belum ada',
            $ada('lainnya', 'Foto Rumah Depan') ? 'Ada' : 'Belum ada',
            $ada('lainnya', 'Foto Rumah Dalam') ? 'Ada' : 'Belum ada',
            $ada('surat_keterangan', 'SKTM') ? 'Ada' : 'Belum ada',
        ];

        $totalDokumen = count(
            array_filter($statusDokumen, fn ($status) => $status === 'Ada')
        );

        $wilayah = $p->wilayah;

        $rw = $wilayah && $wilayah->tipe === 'rt'
            ? $wilayah->induk
            : null;

        $rt = $wilayah && $wilayah->tipe === 'rt'
            ? $wilayah
            : null;

        return [
            (string) $p->nik_decrypted,
            (string) ($p->no_kk_decrypted ?? ''),
            $p->nama,
            $p->tanggal_lahir?->format('Y-m-d'),
            $p->jenis_kelamin,
            (string) $p->nomor_whatsapp,
            $p->alamat,
            (string) ($rw?->nama ?? ''),
            (string) ($rt?->nama ?? ''),
            $p->penghasilan_bulanan,
            $p->jumlah_tanggungan,
            $p->kondisi_rumah,
            $p->disabilitas ? 'Ya' : 'Tidak',
            $p->lansia ? 'Ya' : 'Tidak',
            $p->jenisBantuan?->nama_program,
            $p->status,
            ...$statusDokumen,
            (string) $totalDokumen,
            $totalDokumen === 6 ? 'Lengkap' : 'Belum lengkap',
        ];
    }

    /**
     * Memaksa kolom identitas dan wilayah menjadi teks.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        $kolomTeks = [
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I',
            'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X',
        ];

        if (in_array($cell->getColumn(), $kolomTeks, true)) {
            $cell->setValueExplicit(
                $value === null ? '' : (string) $value,
                DataType::TYPE_STRING
            );

            return true;
        }

        return parent::bindValue($cell, $value);
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
}
