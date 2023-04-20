<?php

namespace App\Http\Controllers\API;

use App\Models\PdfFile;
use App\Models\PdfSentence;
use App\Http\Resources\PdfFile as PdfFileResource;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Http\Controllers\Controller;
use Kreait\Laravel\Firebase\Facades\Firebase;




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
        $name = time() . '.' . $file->extension();
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

    private function getPageCount($path)
    {
        return (new Parser())->parseFile(storage_path('app/' . $path))->getDetails()['Pages'];
    }

    // Get all pdf files
    public function index()
    {
        $pdfs = PdfFileResource::collection(PdfFile::all());
        return response()->json($pdfs, 200);
    }

    // Search for keyword in PdfSentence model and retrieve associated PdfFile models
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



    // Get sentences for a specific pdf file
    public function getPdfSentences($pdfId)
    {
        $pdfFile = PdfFile::find($pdfId);

        if ($pdfFile) {
            $sentences = $pdfFile->sentences()->pluck('sentence')->toArray();

            return response()->json($sentences, 200);
        }

        return response()->json(['message' => 'PDF not found.'], 404);
    }


    // Get top 10 words for a specific pdf file
    public function getTopWords($pdfId)
    {
        $pdfSentences = PdfSentence::where('pdf_file_id', $pdfId)->get();
        $wordCount = [];
   
        	$stopWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero','The');


        foreach ($pdfSentences as $pdfSentence) {
            $words = str_word_count($pdfSentence->sentence, 1);
            foreach ($words as $word) {
                if (!in_array($word, $stopWords)) {
                    $wordCount[$word] = isset($wordCount[$word]) ? $wordCount[$word] + 1 : 1;
                }
            }
        }

        arsort($wordCount);
        $topWords = array_slice($wordCount, 0, 5, true);

        return response()->json([
            'pdf_id' => $pdfId,
            'top_words' => $topWords
        ]);
    }

    // Search for a word in a pdf file
    public function searchWord(Request $request, $pdfId) 
{
    $word = $request->input('word');
    $pdfSentences = PdfSentence::where('pdf_file_id', $pdfId)
                               ->where('sentence', 'LIKE', '%' . $word . '%')
                               ->get();
    
    $count = count($pdfSentences);
    
    $sentences = [];
    foreach ($pdfSentences as $pdfSentence) {
        $sentences[] = $pdfSentence->sentence;
    }
    
    return response()->json([
        'word' => $word,
        'count' => $count,
        'sentences' => $sentences
    ]);
}


public function destroy($id)
    {
        $pdfFile = PdfFile::findOrFail($id);
        $pdfFile->delete();
        return response()->json([
            'message' => 'Pdf file deleted successfully'
        ]);
}


public function download($pdfId)
{
    $pdf = PdfFile::findOrFail($pdfId);

    $url = Firebase::storage()->getBucket()->object("pdfs/".$pdf->name)->signedUrl(now()->addMinutes(5));

    // Return the download URL
    return response()->json([
        'url' => $url,
    ]);
}





}
