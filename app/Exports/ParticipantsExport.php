<?php

namespace App\Exports;

use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Event\AfterSheet;

class ParticipantsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $rowNumber = 1;

    public function collection()
    {
        return Participant::select('name', 'email', 'checked_in_at')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Check-in Status',
        ];
    }

    public function map($participant): array
    {
        $this->rowNumber++;
        return [
            $participant->name,
            $participant->email,
            $participant->checked_in_at ? 'Arrived' : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            1 => ['font' => ['bold' => true]],
        ];

        foreach (range(2, $sheet->getHighestRow()) as $row) {
            if ($sheet->getCell("C{$row}")->getValue() === 'Arrived') {
                $styleArray["C{$row}"] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '90EE90'],
                    ],
                ];
            }
        }

        return $styleArray;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);

                $sheet->getDefaultRowDimension()->setRowHeight(30);

                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->getStyle('A1:C' . $sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
