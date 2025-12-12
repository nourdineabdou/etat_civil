<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NniController extends Controller
{
    public function showForm()
    {
        return view('nni');
    }

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

        // Save to session history
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
        $history = session()->get('nni_lookups', []);

        if (empty($history)) {
            // seed some sample data so the user can see stats immediately
            $history = $this->seedSampleData();
            session()->put('nni_lookups', $history);
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
}
