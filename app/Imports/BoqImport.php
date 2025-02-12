<?php
namespace App\Imports;

use App\Models\Project;
use App\Models\Section;
use App\Models\BoqItem;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BoqImport implements WithMultipleSheets, WithStartRow, WithHeadingRow
{
    private $project;
    private $currentSection;

    public function __construct()
    {
        // Create project first
        $this->project = Project::create([
            'name' => '',  // Will update after reading
            'address' => '',
            'nature_of_project' => ''
        ]);
    }

    public function sheets(): array
    {
        return [
            0 => $this->importSheet()
        ];
    }

    public function startRow(): int
    {
        return 1;
    }

    private function importSheet()
    {
        return function($row) {
            // Extract project details from specific rows
            if ($this->isProjectDetail($row)) {
                $this->updateProjectDetails($row);
                return null;
            }

            // Check if this is a section header
            if ($this->isSectionHeader($row)) {
                $this->currentSection = $this->createSection($row);
                return null;
            }

            // Process BOQ item if it has required data
            if ($this->isBoqItem($row)) {
                return $this->createBoqItem($row);
            }
        };
    }

    private function isProjectDetail($row)
    {
        // Add logic to identify project detail rows
        return !empty($row[1]) && (
            str_contains($row[1], 'Project Name:') ||
            str_contains($row[1], 'Address:') ||
            str_contains($row[1], 'Nature of Project:')
        );
    }

    private function updateProjectDetails($row)
    {
        // Extract and update project details
        $description = $row[1];
        if (str_contains($description, 'Project Name:')) {
            $this->project->name = trim(str_replace('Project Name:', '', $description));
        } elseif (str_contains($description, 'Address:')) {
            $this->project->address = trim(str_replace('Address:', '', $description));
        } elseif (str_contains($description, 'Nature of Project:')) {
            $this->project->nature_of_project = trim(str_replace('Nature of Project:', '', $description));
        }
        $this->project->save();
    }

    private function isSectionHeader($row)
    {
        return !empty($row[1]) && str_contains($row[1], 'BILL No.');
    }

    private function createSection($row)
    {
        $billInfo = explode('-', $row[1], 2);
        return Section::create([
            'project_id' => $this->project->id,
            'bill_no' => trim($billInfo[0]),
            'title' => isset($billInfo[1]) ? trim($billInfo[1]) : '',
            'section_total' => 0
        ]);
    }

    private function isBoqItem($row)
    {
        return !empty($row[0]) && ($row[4] || $row[5] || $row[6] || $row[7]);
    }

    private function createBoqItem($row)
    {
        return BoqItem::create([
            'section_id' => $this->currentSection->id,
            'item_no' => $row[0],
            'description' => $row[1],
            'quantity' => $this->parseNumber($row[4]),
            'unit' => $row[5],
            'rate' => $this->parseNumber($row[6]),
            'amount' => $this->parseNumber($row[7]),
            'time_related_charges' => $this->parseNumber($row[8]),
            'fixed_charges' => $this->parseNumber($row[9])
        ]);
    }

    private function parseNumber($value)
    {
        if (empty($value)) return 0;
        return is_numeric($value) ? $value : 0;
    }
}