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
            $project->nature = $request->category;
            $project->start = $request->start_date;
            $project->end = $request->end_date;
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

    function uploadBoQ(Request $request){
        $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls'])
            ->max(10 * 1024)]
        ]);

        // try {
            // $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            // $Reader->setReadDataOnly( false );
            // $spreadSheet = $Reader->load($request->file);
            $file = $request->file;
            
            $filetype = $file->getClientOriginalExtension();
            
            if ($filetype == 'csv') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
            }
            else if ($filetype == 'xlsx') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            }
            else if ($filetype == 'xls') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
            }
            
            /**  Load $inputFileName to a Spreadsheet Object  **/
            $spreadsheet = $reader->load($request->file);

            $excelSheet = $spreadsheet->getActiveSheet();
            $spreadSheetAry = $excelSheet->toArray();
            $maxCell = $excelSheet->getHighestRowAndColumn();
            $data = $excelSheet->rangeToArray( 'A1:' . $maxCell['column'] . $maxCell['row'] );
            $data = array_map( 'array_filter', $data );
            $data = array_filter($data);
            $title = self::getValuesBetweenTitleAndAddress($data);

            dd($title);

            
        // } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        //     return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        // } catch (\Exception $e) {
        //     return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        // }
            
    }

    function getValuesBetweenTitleAndAddress($array) {
        $startIndex = null;
        $endIndex = null;
        $values = [];

        // Find the start and end indexes
        foreach ($array as $index => $item) {
            if (isset($item[1]) && strtolower($item[1]) == 'title') {
                $startIndex = $index;
            }
            if (isset($item[1]) && strtolower($item[1]) == 'address:') {
                $endIndex = $index;
                break;  // Stop when we find Address
            }
        }

        // If both indexes are found, extract the values between them
        if ($startIndex !== null && $endIndex !== null) {
            // Get values from startIndex + 1 to endIndex - 1
            for ($i = $startIndex + 1; $i < $endIndex; $i++) {
                if(isset($array[$i])){
                    $values[] = $array[$i];
                }
            }
        }

        return $values;
    }
}
