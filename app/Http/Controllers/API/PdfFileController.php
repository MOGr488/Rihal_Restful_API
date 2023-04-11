<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PdfFile;
use Illuminate\Http\Request;
use \Howtomakeaturn\PDFInfo\PDFInfo;



class PdfFileController extends Controller
{
    public function upload(Request $request){
        $file = $request->file('file');
        $name = time().'.'.$file->extension();
        $path = $file->store('pdfs');
        
        $pdf = new PdfFile([
            'name' => $name,
            'path' => $path,
            'size' => $file->getSize(),
            'page_count' => $this->getPageCount($path),
        ]);

        $pdf->save();

        return response()->json(['message' => 'File Uploaded Successfuly.'], 201);
    }

    private function getPageCount($path)
    {
        $pdfInfo = new PDFInfo($path);
        return $pdfInfo->pages;
    }

}
