<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoiceExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
    * @return \Illuminate\Contracts\View\View
    */
    public function view(): View
    {
        return view('finance.invoices.invoice_pdf', [
            'invoice' => $this->invoice
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Invoice ' . $this->invoice->id;
    }
}
