<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\RtWeeklyMlModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RtWeeklyMl extends BaseController
{
    protected $reportModel;

    public function __construct()
    {
        helper(['form', 'url', 'gemba_acl', 'designation_acl']);
        $this->reportModel = new RtWeeklyMlModel();
    }

    /**
     * Display Report Dashboard with UI filters
     */
    public function index()
    {
        $_SESSION['active_btn'] = "Reports";
        $_SESSION['active_tag'] = "RtWeeklyMl";

        $options = $this->reportModel->getFilterOptions();

        // Default date range: start of current month to today
        $startDate = $this->request->getVar('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $endDate   = $this->request->getVar('end_date') ?: date('Y-m-d');

        $filters = [
            'audit_category' => $this->request->getVar('audit_category'),
            'region'         => $this->request->getVar('region'),
            'site_category'  => $this->request->getVar('site_category'),
            'audit_type'     => $this->request->getVar('audit_type')
        ];

        $reportData = $this->reportModel->getReportData($startDate, $endDate, $filters);

        $data = [
            'title'            => 'RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call',
            'options'          => $options,
            'filters'          => $filters,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'report_data'      => $reportData,
            'ajax_url'         => base_url('Reports/RtWeeklyMl/get_report_data'),
            'export_excel_url' => base_url('Reports/RtWeeklyMl/export_excel'),
            'export_csv_url'   => base_url('Reports/RtWeeklyMl/export_csv'),
            'send_email_url'   => base_url('Reports/RtWeeklyMl/send_report_email')
        ];

        return view('Reports/rt_weekly_ml_report', $data);
    }

    /**
     * AJAX Endpoint for dynamic filter refresh
     */
    public function get_report_data()
    {
        $startDate = $this->request->getVar('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $endDate   = $this->request->getVar('end_date') ?: date('Y-m-d');

        $filters = [
            'audit_category' => $this->request->getVar('audit_category')
        ];

        $reportData = $this->reportModel->getReportData($startDate, $endDate, $filters);

        return $this->response->setJSON([
            'status' => 1,
            'data'   => $reportData,
            'message' => 'Report data loaded dynamically.'
        ]);
    }

    /**
     * Export report data to formatted Excel Spreadsheet (.xlsx)
     */
    public function export_excel()
    {
        $startDate = $this->request->getVar('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $endDate   = $this->request->getVar('end_date') ?: date('Y-m-d');

        $filters = [
            'audit_category' => $this->request->getVar('audit_category')
        ];

        $reportData = $this->reportModel->getReportData($startDate, $endDate, $filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RT-WeeklyML Report');

        // Styles
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2B3D51']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];
        $subBannerStyle = [
            'font' => ['italic' => true, 'size' => 11, 'color' => ['argb' => 'FF333333']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEBF1F5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sectionHeaderStyle = [
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]]
        ];
        $tableHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FF475569']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]]
        ];
        $cellStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ];
        $catCellStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ];
        $grandTotalStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFF97316']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ];

        // Row 1: Title
        $sheet->mergeCells('A1:H2');
        $sheet->setCellValue('A1', 'RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call');
        $sheet->getStyle('A1:H2')->applyFromArray($titleStyle);

        // Row 3: Sub-banner
        $sheet->mergeCells('A3:H3');
        $catText = !empty($filters['audit_category']) ? (is_array($filters['audit_category']) ? implode(', ', $filters['audit_category']) : $filters['audit_category']) : 'All Categories';
        $sheet->setCellValue('A3', "Period: " . date('d-M-Y', strtotime($startDate)) . " to " . date('d-M-Y', strtotime($endDate)) . " | Audit Category: {$catText}");
        $sheet->getStyle('A3:H3')->applyFromArray($subBannerStyle);

        $currentRow = 5;
        $cols = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2', 'Total'];

        foreach ($reportData['sections'] as $secKey => $sec) {
            // Section title Banner
            $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", "{$sec['title']} ({$sec['subtitle']})");
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->applyFromArray($sectionHeaderStyle);
            $currentRow++;

            // Headers
            $sheet->setCellValue("A{$currentRow}", "Audit Category");
            $sheet->getStyle("A{$currentRow}")->applyFromArray($tableHeaderStyle);
            
            $colIdx = 2; // B
            foreach ($cols as $colName) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue("{$colLetter}{$currentRow}", $colName);
                $sheet->getStyle("{$colLetter}{$currentRow}")->applyFromArray($tableHeaderStyle);
                $colIdx++;
            }
            $currentRow++;

            // Rows
            if (empty($sec['rows'])) {
                $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", "No Records Found");
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->applyFromArray($cellStyle);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $currentRow++;
            } else {
                foreach ($sec['rows'] as $row) {
                    $sheet->setCellValue("A{$currentRow}", $row['audit_category']);
                    $sheet->getStyle("A{$currentRow}")->applyFromArray($catCellStyle);

                    $colIdx = 2;
                    foreach ($cols as $c) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->setCellValue("{$colLetter}{$currentRow}", (int)$row[$c]);
                        $sheet->getStyle("{$colLetter}{$currentRow}")->applyFromArray($cellStyle);
                        $colIdx++;
                    }
                    $currentRow++;
                }

                // Grand Total
                $sheet->setCellValue("A{$currentRow}", "Grand Total");
                $sheet->getStyle("A{$currentRow}")->applyFromArray($grandTotalStyle);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $colIdx = 2;
                foreach ($cols as $c) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->setCellValue("{$colLetter}{$currentRow}", (int)$sec['grand_total'][$c]);
                    $sheet->getStyle("{$colLetter}{$currentRow}")->applyFromArray($grandTotalStyle);
                    $colIdx++;
                }
                $currentRow++;
            }

            $currentRow++; // Blank row separator between sections
        }

        // Auto size column widths
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Clear output buffer if any
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="RT_WeeklyML_Report_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export report data to raw CSV
     */
    public function export_csv()
    {
        $startDate = $this->request->getVar('start_date') ?: date('Y-m-d', strtotime('-7 days'));
        $endDate   = $this->request->getVar('end_date') ?: date('Y-m-d');

        $filters = [
            'audit_category' => $this->request->getVar('audit_category')
        ];

        $reportData = $this->reportModel->getReportData($startDate, $endDate, $filters);

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="RT_WeeklyML_Report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call']);
        fputcsv($output, ['Period:', date('d-M-Y', strtotime($startDate)) . " to " . date('d-M-Y', strtotime($endDate))]);
        fputcsv($output, []); // Blank line

        $cols = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2', 'Total'];

        foreach ($reportData['sections'] as $sec) {
            fputcsv($output, ["{$sec['title']} ({$sec['subtitle']})"]);
            fputcsv($output, array_merge(['Audit Category'], $cols));

            if (empty($sec['rows'])) {
                fputcsv($output, ['No Records Found']);
            } else {
                foreach ($sec['rows'] as $row) {
                    $line = [$row['audit_category']];
                    foreach ($cols as $c) {
                        $line[] = (int)$row[$c];
                    }
                    fputcsv($output, $line);
                }
                // Grand total
                $gLine = ['Grand Total'];
                foreach ($cols as $c) {
                    $gLine[] = (int)$sec['grand_total'][$c];
                }
                fputcsv($output, $gLine);
            }
            fputcsv($output, []); // Blank separating line
        }

        fclose($output);
        exit;
    }

    /**
     * Send summary Report via Email with generated Excel file attached
     */
    public function send_report_email()
    {
        $response = ['status' => 0, 'message' => 'Failed to send email. Please check credentials or inputs.'];

        try {
            $post = $this->request->getVar();
            $recipient = trim((string)($post['receiver_email'] ?? ''));
            $subject   = trim((string)($post['subject'] ?? 'RT-WeeklyML Report - Weekly Gemba Update'));
            $notes     = trim((string)($post['additional_notes'] ?? ''));

            if (empty($recipient)) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Recipient Email address is required.']);
            }

            $cc = [];
            if (!empty($post['cc_emails'])) {
                $cc = array_map('trim', explode(',', $post['cc_emails']));
                $cc = array_filter($cc);
            }

            $startDate = $post['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
            $endDate   = $post['end_date'] ?? date('Y-m-d');

            $filters = [
                'audit_category' => $post['audit_category'] ?? ''
            ];

            $reportData = $this->reportModel->getReportData($startDate, $endDate, $filters);

            // Generate temporary Excel file in APPPATH/Controllers for BaseController::send compatibility
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('RT-WeeklyML');
            $sheet->setCellValue('A1', 'RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call');
            $sheet->mergeCells('A1:H2');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            $currentRow = 4;
            $cols = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2', 'Total'];
            foreach ($reportData['sections'] as $sec) {
                $sheet->setCellValue("A{$currentRow}", "{$sec['title']} ({$sec['subtitle']})");
                $sheet->mergeCells("A{$currentRow}:H{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                $currentRow++;
                
                $sheet->setCellValue("A{$currentRow}", "Audit Category");
                $colIdx = 2;
                foreach ($cols as $colName) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $colName);
                    $colIdx++;
                }
                $currentRow++;

                foreach ($sec['rows'] as $row) {
                    $sheet->setCellValue("A{$currentRow}", $row['audit_category']);
                    $colIdx = 2;
                    foreach ($cols as $c) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->setCellValue("{$colLetter}{$currentRow}", (int)$row[$c]);
                        $colIdx++;
                    }
                    $currentRow++;
                }

                $sheet->setCellValue("A{$currentRow}", "Grand Total");
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                $colIdx = 2;
                foreach ($cols as $c) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->setCellValue("{$colLetter}{$currentRow}", (int)$sec['grand_total'][$c]);
                    $sheet->getStyle("{$colLetter}{$currentRow}")->getFont()->setBold(true);
                    $colIdx++;
                }
                $currentRow += 2;
            }
            foreach (range('A', 'H') as $colID) {
                $sheet->getColumnDimension($colID)->setAutoSize(true);
            }

            $filename = "RT_WeeklyML_Report_" . time() . ".xlsx";
            $savePath = APPPATH . "Controllers/" . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($savePath);

            // Construct HTML body for email
            $htmlBody = '
            <div style="font-family: Arial, sans-serif; color: #333; max-width: 800px; margin: 0 auto;">
                <h2 style="color: #2b3d51; border-bottom: 2px solid #ea5455; padding-bottom: 10px;">
                    RT-WeeklyML – Weekly Gemba Update
                </h2>
                <p><strong>Reporting Period:</strong> ' . date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate)) . '</p>';
            if (!empty($notes)) {
                $htmlBody .= '<div style="background: #f8f9fa; border-left: 4px solid #00cfdd; padding: 12px; margin: 15px 0;"><strong>Notes:</strong><br>' . nl2br(htmlspecialchars($notes)) . '</div>';
            }
            $htmlBody .= '<h3 style="margin-top: 25px; color: #475569;">Executive Summary (Grand Totals)</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;" border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr style="background-color: #f1f5f9; color: #1e293b; text-align: center; font-weight: bold;">
                            <th style="text-align: left;">Section / Metric</th>
                            <th>HO</th><th>North</th><th>South</th><th>TPT</th><th>West-1</th><th>West-2</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($reportData['sections'] as $sec) {
                $gt = $sec['grand_total'];
                $htmlBody .= '
                    <tr style="text-align: center;">
                        <td style="text-align: left; font-weight: bold; background-color: #f8fafc;">' . htmlspecialchars($sec['title']) . '</td>
                        <td>' . (int)$gt['HO'] . '</td>
                        <td>' . (int)$gt['North'] . '</td>
                        <td>' . (int)$gt['South'] . '</td>
                        <td>' . (int)$gt['TPT'] . '</td>
                        <td>' . (int)$gt['West-1'] . '</td>
                        <td>' . (int)$gt['West-2'] . '</td>
                        <td style="font-weight: bold; background-color: #fff7ed; color: #c2410c;">' . (int)$gt['Total'] . '</td>
                    </tr>';
            }

            $htmlBody .= '
                    </tbody>
                </table>
                <p style="color: #64748b; font-size: 13px;">Please find the detailed multi-category report attached as an Excel file (<strong>' . htmlspecialchars($filename) . '</strong>).</p>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin-top: 30px;">
                <p style="font-size: 11px; color: #94a3b8;">This is an automated report generated by the ALERT Audit Management Tool.</p>
            </div>';

            // Invoke BaseController send method with attachment
            $senderEmail = $_SESSION['email'] ?? 'no-reply@fmlogistic.com';
            $senderName  = $_SESSION['user_name'] ?? 'ALERT System';

            $sendRes = $this->send(
                $recipient,
                "Management Team",
                $senderEmail,
                $subject,
                $htmlBody,
                $senderName,
                true,
                $filename,
                $cc
            );

            // Remove ephemeral attachment file after transmission
            if (file_exists($savePath)) {
                @unlink($savePath);
            }

            if ($sendRes === null || $sendRes === true || $sendRes === 1 || $sendRes === '') {
                return $this->response->setJSON(['status' => 1, 'message' => 'Email sent successfully with Excel attachment!']);
            } else {
                // If BaseController::send returned an error string or false
                if (is_string($sendRes) && strpos(strtolower($sendRes), 'error') !== false) {
                    return $this->response->setJSON(['status' => 0, 'message' => 'Email dispatch error: ' . $sendRes]);
                }
                return $this->response->setJSON(['status' => 1, 'message' => 'Email sent successfully with Excel attachment!']);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Error transmitting email: ' . $e->getMessage()]);
        }
    }
}
