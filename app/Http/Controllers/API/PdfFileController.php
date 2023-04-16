<?php

namespace App\Http\Controllers\API;

use App\Models\PdfFile;
use App\Models\PdfSentence;
use App\Http\Resources\PdfFile as PdfFileResource;
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
    
       foreach ($pages as $pageNumber => $page) {
        $text = $page->getText();
     
        $sentences = preg_split('/(?<=[.?!])\s+/', $text);
        $sentences = array_filter($sentences, function($sentence) {
            return strlen(trim($sentence)) > 1;
        });
         
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


    public function index(){
        $pdfs = PdfFileResource::collection(PdfFile::all());
        return response()->json($pdfs, 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $pdfFileIdsAndSentences = [];
    
        // Search for keyword in PdfSentence model and retrieve associated PdfFile models
        $pdfSentences = PdfSentence::where('sentence', 'LIKE', '%' . $keyword . '%')->with('pdfFile')->get();

        if ($pdfSentences->isEmpty()) {
            return response()->json(['message' => 'No results found.'], 404);
        }
    
        foreach ($pdfSentences as $pdfSentence) {
            $pdfFile = $pdfSentence->pdfFile;
    
            // Check if PdfFile has already been added to array
            $foundPdfFile = array_filter($pdfFileIdsAndSentences, function ($item) use ($pdfFile) {
                return $item['pdf_file_id'] == $pdfFile->id;
            });
    
            if (empty($foundPdfFile)) {
                // Add PdfFile and sentence to array if not already present
                $pdfFileIdsAndSentences[] = [
                    'pdf_file_id' => $pdfFile->id,
                    'sentences' => [$pdfSentence->sentence],
                ];
            } else {
                // Add sentence to existing PdfFile in array
                foreach ($pdfFileIdsAndSentences as &$item) {
                    if ($item['pdf_file_id'] == $pdfFile->id) {
                        $item['sentences'][] = $pdfSentence->sentence;
                        break;
                    }
                }
            }
        }
    
        return response()->json($pdfFileIdsAndSentences, 200);
    }



}
