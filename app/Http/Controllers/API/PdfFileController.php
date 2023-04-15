<?php

namespace App\Http\Controllers\API;

use App\Models\PdfFile;
use App\Models\PdfSentence;
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

        $parser = new Parser();
        $parsedPdf = $parser->parseFile(storage_path('app/' . $path));
        $pages = $parsedPdf->getPages();
        $sentences = [];
       // dd($pages);
       foreach ($pages as $pageNumber => $page) {
        $text = $page->getText();
        
        $sentences = preg_split('/(?<=[.?!])\s+/', $text);
        $sentences = array_filter($sentences, function($sentence) {
            return strlen(trim($sentence)) > 1;
        });
          //  dd($sentences);
        foreach ($sentences as $sentence) {
            PdfSentence::create([
                'pdf_file_id' => $pdf->id,
                'sentence' => $sentence,
                'page_number' => $pageNumber,
            ]);
        }
    }
    
        return response()->json(['message' => 'File Uploaded Successfuly.'], 201);
    }

    private function getPageCount($path)
    {
        return (new Parser())->parseFile(storage_path('app/' . $path))->getDetails()['Pages'];
    }

}
