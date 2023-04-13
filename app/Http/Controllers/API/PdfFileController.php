<?php

namespace App\Http\Controllers\API;

use App\Models\PdfFile;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Http\Controllers\Controller;




class PdfFileController extends Controller
{
    public function upload(Request $request){
        $file = $request->file('file');
        $name = time().'.'.$file->extension();
        $path = $file->storeAs('pdfs',$name);
        
        $pdf = new PdfFile([
            'name' => $name,
            'user_id' => 1,
            'path' => $path,
            'size' => $file->getSize(),
            'page_count' => $this->getPageCount($path),
        ]);

        $pdf->save();

        return response()->json(['message' => 'File Uploaded Successfuly.'], 201);
    }

    private function getPageCount($path)
    {
        //dd($path);
        $parser = new Parser();
        
        $pdf = $parser->parseFile(storage_path('app/' . $path))->getDetails();
        return $pdf['Pages'];

    }

}
