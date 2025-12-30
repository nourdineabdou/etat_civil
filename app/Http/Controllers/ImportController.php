<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\RhtCongeAgentImporter;

class ImportController extends Controller
{
    public function importRhtCongeAgent(Request $request)
    {
        $this->authorize?->call($this); // noop if no authorize helper

        $truncate = $request->boolean('truncate', false);

        $importer = new RhtCongeAgentImporter();
        try {
            $count = $importer->import($truncate);
            return response()->json([
                'success' => true,
                'inserted_statements' => $count,
                'message' => "Imported {$count} INSERT statements into rhtcongeagent."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
