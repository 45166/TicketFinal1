<?php

namespace App\Exports;

use App\Models\Evaluation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EvaluationsExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * Return all evaluations with necessary fields.
     */
    public function collection()
    {
        return Evaluation::with('repairRequest', 'user')
            ->get()
            ->map(function ($evaluation) {
                return [
                    'Request ID' => $evaluation->repairRequest->TicketNumber,
                    'วันที่แจ้งซ่อม' => $evaluation->repairRequest->created_at->format('Y-m-d'), 
                    'ผู้แจ้งซ่อม' => $evaluation->user->name,
                    'ความพึงพอใจ' => $evaluation->rating,
                ];
            });
    }

    /**
     * Define headings for the Excel file.
     */
    public function headings(): array
    {
        return [
            'Ticket ID',
            'วันที่แจ้งซ่อม',
            'ผู้แจ้งซ่อม',
            'ความพึงพอใจ',
        ];
    }

    /**
     * Apply styles to the spreadsheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Set header styles
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 15,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0070C0'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // Apply styles to the header row
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Set column widths
         $sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(20);

    // Center align the data cells
    $sheet->getStyle('A2:D' . $sheet->getHighestRow())->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Set borders for all cells in the range
    $sheet->getStyle('A1:D' . $sheet->getHighestRow())->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ]);

    // Adjust font size for data rows
    $sheet->getStyle('A2:D' . $sheet->getHighestRow())->getFont()->setSize(11); // เปลี่ยนขนาดฟอนต์ให้เป็น 11

    // Set row height for all data rows
    foreach (range(2, $sheet->getHighestRow()) as $row) {
        $sheet->getRowDimension($row)->setRowHeight(18); // ปรับความสูงแถวให้เป็น 
        }
}
}