<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DelschedFinalExport;
use Maatwebsite\Excel\Excel as ExcelExcel;

use App\Exports\DelschedFinalWipExport;

class DeliveryScheduleMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

   public function build()
    {
        $excelFinal = Excel::raw(new DelschedFinalExport, ExcelExcel::XLSX);
        $excelWip   = Excel::raw(new DelschedFinalWipExport, ExcelExcel::XLSX);

        return $this->subject('Production Delivery Detail H+1 – H+3')
            ->view('mail.delivery-detail')
            ->attachData($excelFinal, 'delsched_final.xlsx', [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->attachData($excelWip, 'delsched_wip.xlsx', [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
