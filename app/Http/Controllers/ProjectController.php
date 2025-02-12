<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\BoqImport;

class ProjectController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx'
        ]);

        DB::beginTransaction();
        try {
            $import = new BoqImport();
            Excel::import($import, $request->file('file'));
            
            // Update totals
            $this->updateTotals();
            
            DB::commit();
            return redirect()->back()->with('success', 'Project BOQ imported successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error importing BOQ: ' . $e->getMessage());
        }
    }

    private function updateTotals()
    {
        // Update section totals
        Section::chunk(100, function($sections) {
            foreach ($sections as $section) {
                $sectionTotal = $section->items()->sum(DB::raw('amount + time_related_charges + fixed_charges'));
                $section->update(['section_total' => $sectionTotal]);
            }
        });

        // Update project totals
        Project::chunk(100, function($projects) {
            foreach ($projects as $project) {
                $projectTotal = $project->sections()->sum('section_total');
                $project->update(['total_amount' => $projectTotal]);
            }
        });
    }

    public function getProjectSummary($projectId)
    {
        $project = Project::with(['sections.items' => function($query) {
            $query->select('id', 'section_id', 'item_no', 'description', 'quantity', 'unit', 'rate', 'amount');
        }])->findOrFail($projectId);

        return response()->json($project);
    }
}
