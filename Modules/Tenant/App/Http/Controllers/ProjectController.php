<?php

namespace Modules\Tenant\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Tenant\App\Models\{User, Project, ProjectManager, ProjectAssignee};
use DB;

use Illuminate\Validation\Rules\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory; 

class ProjectController extends Controller
{
    function index(){
        dd('here');
    }

    function create(Request $request){
        try {

            $managers = User::role('manager')->get()->pluck('name', 'id');

            $team = User::withoutRole('admin')->get()->pluck('name', 'id');
            $priority = [
                'low',
                'medium',
                'High'
            ];
            $category = [
                'groundwork'
            ];

            return response()->json([
                "managers" => $managers,
                "team" => $team,
                "priority" => $priority,
                "category" => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    public function store(Request $request){
        

        DB::beginTransaction();
        try {

            $request->validate([
                'title' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:250',
                'manager' => 'required',
                'category' => 'required',
                'start_date' => 'required|date|before:end_date',
                'end_date' => 'date|after:start_date',
                'budget' => 'required|decimal:2',
                'priority' => 'required',
                'assignee' => 'required|array',
            ]);

            $project = new Project;
            $project->title = $request->title;
            $project->description = $request->description;
            $project->address = $request->address;
            $project->nature = $request->nature;
            $project->start = $request->start;
            $project->budget = $request->budget;
            $project->status = $request->priority;
            $project->created_by = $request->user()->id;
            $project->save();

            if(isset($request->manager) && !empty($request->manager)){
                $manager = new ProjectManager;
                $manager->user_id = $request->manager;
                $manager->project_id = $project->id;
                $manager->created_by = $request->user()->id;
                $manager->save();
            }

            if(isset($request->assignee) && !empty($request->assignee)){
                foreach ($request->assignee as $key => $value) {
                    $assignee = new ProjectAssignee;
                    $assignee->user_id = $value;
                    $assignee->project_id = $project->id;
                    $assignee->created_by = $request->user()->id;
                    $assignee->save();
                }
            }

            DB::commit();
            return response()->json(['user' => $request->all()], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(["message"=> $e->getMessage(), 'error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "errors" => $e->getMessage()], 400);
        }
    }


    // Define the headings we're looking for
    private $headings = [
        'title' => 'Title',
        'address' => 'Address',
        'nature_of_project' => 'Nature of Project',
        'employer' => 'Employer',
        'consultant' => 'Consultant'
    ];

    public function uploadBoQ(Request $request)
    {
        $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls'])
            ->max(10 * 1024)]
        ]);
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $projectDetails = [];
            $headingRows = [];
            
            // First pass: Find the rows where our headings are
            foreach ($worksheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                foreach ($cellIterator as $cell) {
                    $value = trim($cell->getValue());
                    // Remove any colon from the value for comparison
                    $cleanValue = rtrim($value, ':');
                    
                    // Check if this cell contains one of our headings
                    foreach ($this->headings as $key => $heading) {
                        if (strcasecmp($cleanValue, $heading) === 0) {
                            $headingRows[$key] = [
                                'row' => $rowIndex,
                                'column' => $cell->getColumn()
                            ];
                            break;
                        }
                    }
                }
            }
            
            // Second pass: Get the values below each heading
            foreach ($headingRows as $key => $location) {
                $row = $location['row'];
                $column = $location['column'];
                $values = [];
                
                // Keep reading rows until we hit another heading or empty row
                while (true) {
                    $row++;
                    // Try to get value from the next column (B if heading was in A, etc.)
                    $nextColumn = ++$column;
                    --$column; // Reset column for next iteration
                    
                    $cell = $worksheet->getCell($nextColumn . $row);
                    $value = trim($cell->getValue());
                    
                    // Stop if we hit an empty cell or another heading
                    if (empty($value) || $this->isHeading($value)) {
                        break;
                    }
                    
                    $values[] = $value;
                }
                
                // Join multiple lines if necessary
                $projectDetails[$key] = implode("\n", array_filter($values));
            }
            
            return response()->json([
                'success' => true,
                'data' => $projectDetails
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if a value is one of our headings
     */
    private function isHeading($value)
    {
        // Remove any colon from the value
        $cleanValue = rtrim($value, ':');
        
        foreach ($this->headings as $heading) {
            if (strcasecmp($cleanValue, $heading) === 0) {
                return true;
            }
        }
        return false;
    }
}
