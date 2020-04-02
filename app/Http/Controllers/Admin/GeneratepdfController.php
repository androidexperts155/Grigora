<?php

namespace App\Http\Controllers\Admin;
use PDF;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GeneratepdfController extends Controller
{
     public function generatePDF($CODE)
    {
        $data = ['CODE' => $CODE];
        $pdf = PDF::loadView('admin.voucher.viewpdf', $data);
  
        return $pdf->download('vouchercard.pdf');
    }
}
