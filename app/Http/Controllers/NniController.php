<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NniSearch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NniController extends Controller
{
    public function showForm()
    {
        return view('nni');
    }
 /// je veux crrer un

    public function lookup(Request $request)
    {
        $request->validate([
            'nni' => ['required', 'string', 'min:10', 'max:10'],
        ]);

        $nni = trim($request->input('nni'));

        // Configuration SOAP
        $url = 'C:\Users\848295\Desktop\Server.xml';
        $options = [
            'cache_wsdl' => 0,
            'trace' => 1,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]),
            'connection_timeout' => 200
        ];

        try {
            $client = new \SoapClient($url, $options);

            // Appel SOAP
            $response = $client->__soapCall('getPersonneToPhoneOperator', [
                'arg0' => $nni,
                'arg1' => '',
                'arg2' => 'ATTIJARI_BANK_12QRCV21'
            ]);

            // Vérifier si la réponse contient une erreur
            if (!isset($response->return) || $response->return->codeError != 2) {
                return response()->json([
                    'error' => true,
                    'message' => $response->return->messageError ?? 'NNI introuvable ou erreur serveur'
                ], 404);
            }

            $person = $response->return;

            // Construire les données
            $data = [
                'nni' => $person->nni ?? $nni,
                'photo' => $this->convertImageToDataUrl($person->img ?? null),
                'nom_fr' => $person->nomFamilleFr ?? '',
                'nom_ar' => $person->nomFamilleAr ?? '',
                'prenom_fr' => $person->prenomFr ?? '',
                'prenom_ar' => $person->prenomAr ?? '',
                'nom_famille_fr' => $person->prenomPereFr ?? '',
                'nom_famille_ar' => $person->prenomPereAr ?? '',
                'date_naissance' => isset($person->dateNaissance) ? date('Y-m-d', strtotime($person->dateNaissance)) : '',
                'lieu_naissance_fr' => $person->lieuNaissanceFr ?? '',
                'lieu_naissance_ar' => $person->lieuNaissanceAr ?? '',
            ];

        } catch (\Exception $e) {
            // En cas d'erreur SOAP, retourner une erreur
            return response()->json([
                'error' => true,
                'message' => 'Erreur de connexion au service: ' . $e->getMessage()
            ], 500);
        }

        // Save to database (non-blocking)
        try {
            NniSearch::create([
                'nni' => $data['nni'],
                'nom_fr' => $data['nom_fr'] ?? null,
                'prenom_fr' => $data['prenom_fr'] ?? null,
                'date_naissance' => $data['date_naissance'] ?: null,
                'lieu_naissance_fr' => $data['lieu_naissance_fr'] ?? null,
                'ip' => $request->ip(),
                'user_id' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save NNI search: ' . $e->getMessage());
        }

        // Save to session history (kept for compatibility)
        $entry = [
            'nni' => $data['nni'],
            'nom_fr' => $data['nom_fr'],
            'date_naissance' => $data['date_naissance'],
            'lieu_naissance_fr' => $data['lieu_naissance_fr'],
            'created_at' => now()->toDateTimeString(),
        ];

        $history = session()->get('nni_lookups', []);
        $history[] = $entry;
        session()->put('nni_lookups', $history);

        $stats = $this->computeStats($history);

        return response()->json(array_merge($data, ['stats' => $stats]));
    }

    private function convertImageToDataUrl($base64Image)
    {
        if (empty($base64Image)) {
            // Retourner une image par défaut si aucune image
            return $this->generatePhotoDataUrl('NOIMG');
        }

        // L'image est déjà en base64, on la convertit en data URL
        return 'data:image/jpeg;base64,' . $base64Image;
    }

    public function stats()
    {
        // Read from DB and map to the same structure used by computeStats()
        $records = NniSearch::orderBy('created_at', 'desc')->get();

        if ($records->isEmpty()) {
            $history = $this->seedSampleData();
        } else {
            $history = $records->map(function ($r) {
                return [
                    'nni' => $r->nni,
                    'nom_fr' => $r->nom_fr,
                    'date_naissance' => $r->date_naissance ? $r->date_naissance->toDateString() : '',
                    'lieu_naissance_fr' => $r->lieu_naissance_fr,
                    'created_at' => $r->created_at->toDateTimeString(),
                ];
            })->toArray();
        }

        $stats = $this->computeStats($history);
        return response()->json(['stats' => $stats]);
    }



    private function computeStats(array $history): array
    {
        $total = count($history);
        $uniqueNnis = count(array_unique(array_map(function ($e) { return $e['nni']; }, $history)));

        $years = [];
        $places = [];
        foreach ($history as $h) {
            $yr = null;
            if (!empty($h['date_naissance'])) {
                $yr = substr($h['date_naissance'], 0, 4);
            }
            if ($yr) {
                $years[] = $yr;
            }
            if (!empty($h['lieu_naissance_fr'])) {
                $places[] = $h['lieu_naissance_fr'];
            }
        }

        $byYear = array_count_values($years);
        ksort($byYear);

        $byPlace = array_count_values($places);
        arsort($byPlace);
        $topPlaces = array_slice($byPlace, 0, 5, true);

        return [
            'total_lookups' => $total,
            'unique_nnis' => $uniqueNnis,
            'by_year' => $byYear,
            'top_places' => $topPlaces,
        ];
    }

    private function seedSampleData(): array
    {
        $samples = [
            ['nni' => '11112222', 'nom_fr' => 'El 2222 (FR)', 'date_naissance' => '1985-03-12', 'lieu_naissance_fr' => 'Rabat', 'created_at' => now()->subDays(5)->toDateTimeString()],
            ['nni' => '22223333', 'nom_fr' => 'El 3333 (FR)', 'date_naissance' => '1990-05-14', 'lieu_naissance_fr' => 'Casablanca', 'created_at' => now()->subDays(4)->toDateTimeString()],
            ['nni' => '33334444', 'nom_fr' => 'El 4444 (FR)', 'date_naissance' => '1992-07-20', 'lieu_naissance_fr' => 'Rabat', 'created_at' => now()->subDays(3)->toDateTimeString()],
            ['nni' => '44445555', 'nom_fr' => 'El 5555 (FR)', 'date_naissance' => '1988-11-01', 'lieu_naissance_fr' => 'Fes', 'created_at' => now()->subDays(2)->toDateTimeString()],
            ['nni' => '55556666', 'nom_fr' => 'El 6666 (FR)', 'date_naissance' => '1995-02-28', 'lieu_naissance_fr' => 'Marrakech', 'created_at' => now()->subDay()->toDateTimeString()],
        ];

        return $samples;
    }

    private function generatePhotoDataUrl(string $nni): string
    {
        $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nni), 0, 2) ?: 'NN');
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='400' height='520'>";
        $svg .= "<rect width='100%' height='100%' fill='#f7fafc'/>";
        $svg .= "<rect x='20' y='20' width='360' height='480' rx='12' ry='12' fill='#ffffff' stroke='#e2e8f0'/>";
        $svg .= "<circle cx='200' cy='160' r='90' fill='#f6ad55'/>";
        $svg .= "<text x='200' y='180' font-size='64' text-anchor='middle' fill='#ffffff' font-family='Arial, Helvetica, sans-serif' font-weight='700'>{$initials}</text>";
        $svg .= "<text x='200' y='350' font-size='22' text-anchor='middle' fill='#2d3748' font-family='Arial, Helvetica, sans-serif'>NNI: {$nni}</text>";
        $svg .= "</svg>";

        $base64 = base64_encode($svg);
        return "data:image/svg+xml;base64,{$base64}";
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget('nni_lookups');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect('/login');
    }

    /**
     * Return JSON data for charts:
     * - last4Months: labels, counts, cumulative
     * - current: daily/weekly/monthly cumulative series
     */
    public function chartsData()
    {
        $today = Carbon::today();

        // Last 4 rolling months (including current)
        $last4Labels = [];
        $last4Counts = [];
        $running = 0;
        $last4Cumulative = [];
        for ($i = 3; $i >= 0; $i--) {
            $m = $today->copy()->subMonths($i);
            $label = $m->translatedFormat('M Y');
            $count = NniSearch::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count();
            $running += $count;
            $last4Labels[] = $label;
            $last4Counts[] = $count;
            $last4Cumulative[] = $running;
        }

        // Current month: daily cumulative
        $startMonth = $today->copy()->startOfMonth();
        $dailyLabels = [];
        $dailyCumulative = [];
        $running = 0;
        for ($d = $startMonth->copy(); $d->lte($today); $d->addDay()) {
            $label = $d->format('d/m');
            $cnt = NniSearch::whereDate('created_at', $d->toDateString())->count();
            $running += $cnt;
            $dailyLabels[] = $label;
            $dailyCumulative[] = $running;
        }

        // Current month: weekly cumulative (weeks intersecting current month)
        $weekLabels = [];
        $weekCumulative = [];
        $running = 0;
        $weekStart = $startMonth->copy()->startOfWeek();
        while ($weekStart->lte($today)) {
            $weekEnd = $weekStart->copy()->endOfWeek();
            $periodStart = $weekStart->copy()->max($startMonth);
            $periodEnd = $weekEnd->copy()->min($today);
            if ($periodStart->gt($periodEnd)) { $weekStart->addWeek(); continue; }
            $label = $periodStart->format('d/m') . '—' . $periodEnd->format('d/m');
            $cnt = NniSearch::whereBetween('created_at', [$periodStart->startOfDay()->toDateTimeString(), $periodEnd->endOfDay()->toDateTimeString()])->count();
            $running += $cnt;
            $weekLabels[] = $label;
            $weekCumulative[] = $running;
            $weekStart->addWeek();
        }

        // Months of current year up to current month: cumulative
        $monthLabels = [];
        $monthCumulative = [];
        $running = 0;
        for ($m = 1; $m <= intval($today->month); $m++) {
            $cnt = NniSearch::whereYear('created_at', $today->year)->whereMonth('created_at', $m)->count();
            $running += $cnt;
            $monthLabels[] = Carbon::create($today->year, $m, 1)->translatedFormat('M');
            $monthCumulative[] = $running;
        }

        return response()->json([
            'last4Months' => [
                'labels' => $last4Labels,
                'counts' => $last4Counts,
                'cumulative' => $last4Cumulative,
            ],
            'current' => [
                'daily' => ['labels' => $dailyLabels, 'cumulative' => $dailyCumulative],
                'weekly' => ['labels' => $weekLabels, 'cumulative' => $weekCumulative],
                'monthly' => ['labels' => $monthLabels, 'cumulative' => $monthCumulative],
            ],
            'summary' => [
                'month_total' => NniSearch::whereYear('created_at', $today->year)->whereMonth('created_at', $today->month)->count(),
                'week_total' => NniSearch::whereBetween('created_at', [$today->copy()->startOfWeek()->startOfDay()->toDateTimeString(), $today->endOfDay()->toDateTimeString()])->count(),
                'today_total' => NniSearch::whereDate('created_at', $today->toDateString())->count(),
            ],
        ]);
    }

    //  fonction pour importer les donnes  partir d'un fichier excel
    // en utilisant RhtCongeAgentImporter
    // public function importRhtCongeAgent($path ="C:\Users\Administrateur\Downloads\conges a inserer 261225.xlsx")
    // {
    //     $importer = new \App\Imports\RhtCongeAgentImporter();

    //     try {
    //         $count = $importer->importExcel($path, true);

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Importation réussie. Nombre d'enregistrements insérés: {$count}"
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de l\'importation: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}
