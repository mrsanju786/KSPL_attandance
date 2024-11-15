<?php

namespace App\Models;


use DB;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;

// class AttendanceExport implements FromCollection ,WithHeadings
class AttendanceDailyExport implements  WithHeadings,FromArray
{
    protected $data;
    public function __construct($start_month,$end_month,array $data) {     
        
        $this->start_month=$start_month;
        $this->end_month=$end_month;
        $this->data=$data;     
    }

    public function headings(): array
    {
        $period = CarbonPeriod::create($this->start_month, $this->end_month);

        // $dates=[];
        $dynamic_headings=[];

        foreach ($period as $date) {
            array_push($dynamic_headings,$date->format('j-F-Y'));
        }

        $static_headings_start= [
            'Employee Code',
            'Employee Name',
            'Rsm Name',
            'User Status',
            'Sole Id',
            'Woker Device ID',
            'In Location',
        ];

        $static_headings_end= [
            'Payable Days (P+LT+OD+H+WO)',
            'Present',
            'Late',
            'Out Door',
            'Week Off',
            'Leave',
            'Holiday',
            'Absent',  
        ];

        return array_merge($static_headings_start,$dynamic_headings ,$static_headings_end);
    }

    public function array(): array
    {
        return $this->data;
    }
}
