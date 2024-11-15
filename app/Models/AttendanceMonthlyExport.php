<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;

class AttendanceMonthlyExport implements FromCollection
{
    // use Exportable;

    public function __construct($attendances) {
        $this->attendances = $attendances;    
    }
    
    public function collection()
    {
        // return Attendance::all();
       
    }


    public function headings(): array
    {
         return [
            'name',
            'date',
            'worker_device_id',
            'role',
            'in_time',
            'out_time',
            'work_hour',
            'over_time',
            'late_time',
            'early_out_time',
            'in_location_id',
            'out_location_id',
            'status',
            'status_updated_at',
            'status_updated_by',

        ];
    }

    /**
    * @var Attendance $attendance
    */
    public function map($attendance): array
    {

      return [
        $attendance->name,
        $attendance->date,
        $attendance->worker_device_id,
        $attendance->role,
        $attendance->in_time,
        $attendance->out_time,
        $attendance->work_hour,
        $attendance->over_time,
        $attendance->late_time,
        $attendance->early_out_time,
        $attendance->in_location_id,
        $attendance->out_location_id,
        $attendance->status,
        $attendance->status_updated_at,
        $attendance->status_updated_by,
        ];
    }
}