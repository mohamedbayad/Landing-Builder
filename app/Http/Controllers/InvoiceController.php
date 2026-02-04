<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(Request $request, Lead $lead)
    {
        // Security: Ensure the URL is valid (signed)
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', compact('lead'));

        return $pdf->download('invoice-' . $lead->transaction_id . '.pdf');
    }
}
