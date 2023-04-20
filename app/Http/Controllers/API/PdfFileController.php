<?php

namespace App\Http\Controllers\API;

use App\Models\PdfFile;
use App\Models\PdfSentence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Http\Controllers\Controller;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Http\Resources\PdfFile as PdfFileResource;




class PdfFileController extends Controller
{
    /**
     * Upload a pdf file
     *
     * @param Request $request
     * @return json
     */
    public function upload(Request $request)
    {
       
        $file = $request->file('file');
        $name = random_int(1, 100).'-'.Str::slug($file->getClientOriginalName()).'.'.$file->extension();
        $path = $file->storeAs('pdfs', $name);


        $firebaseStorage = Firebase::storage();
        $bucket = $firebaseStorage->getBucket();
        $bucket->upload(file_get_contents(storage_path('app/' . $path)), [
            'name' => 'pdfs/' . $name,
        ]);


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
            $sentences = array_filter($sentences, function ($sentence) {
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

    /**
     * Get the number of pages in a pdf file
     *
     * @param string $path
     * @return int
     */
    private function getPageCount($path)
    {
        return (new Parser())->parseFile(storage_path('app/' . $path))->getDetails()['Pages'];
    }

    /**
     * Get all pdf files
     * 
     * @return json
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') <= 50 ? $request->input('limit') : 15;
        $pdfs = PdfFileResource::collection(PdfFile::simplePaginate($limit));
         return $pdfs->response()
        ->setStatusCode(200, "PDFs Returned Successfully");
    }



    /**
     * Search for a keyword in all pdf files
     *
     * @param string $keyword
     * @return json
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $pdfFileIdsAndSentences = [];

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

    /**
     * Delete pdf given its id
     *
     * @param pdf $id
     * @return json
     */
    public function destroy($id)
    {
        $pdfFile = PdfFile::findOrFail($id);
        $pdfFile->delete();
        return response()->json([
            'message' => 'Pdf file deleted successfully'
        ]);
    }


    /**
     * Download pdf given its id
     *
     * @param pdf $id
     * @return json
     */

    public function download($pdfId)
    {
        $pdf = PdfFile::findOrFail($pdfId);
        
        $url = Firebase::storage()->getBucket()->object("pdfs/" . $pdf->name)->signedUrl(now()->addMinutes(5));

        // Return the download URL
        return response()->json([
            'url' => $url,
        ]);
    }
}
