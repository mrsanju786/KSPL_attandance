{{-- resources/views/admin/dashboard.blade.php --}}
@extends('adminlte::page')
@section('title', 'Dashboard  | ' . Config::get('adminlte.title'))
@section('content_header')
<h1>Dashboard<span>&#8208;</span>{{date('d-m-Y')}}</h1>
@stop
@section('content')

<style>
.brand-link .brand-image {
   border-radius: 0px;
    width: auto;
    background: white;
    box-shadow: none !important;
}
</style>

<!-- <div class="card">
   <div class="card-body">
       @if (session('status'))
           <div class="alert alert-success" role="alert">
               {{ session('status') }}
           </div>
       @endif
   
       Hi, and Welcome!
   </div>
   </div> -->
<div class="row">
   @if(Auth::user()->hasRole('administrator'))
   <div class="col-lg-5 col-5" style="max-width: 35.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
        <div class="row">
            <div class="inner" style="margin-left: 16px;">
               <h3>{{ $userCount }}</h3>
               <p style="font-size:larger;">Total Users</p>
            </div>
            <div class="inner" style="margin-left: 16px;">
               <h3>{{ $activeUser }}</h3>
               <p style="font-size:larger;">Active Users</p>
            </div>
            <div class="inner" style="margin-left: 16px;">
               <h3>{{ $deactiveUser }}</h3>
               <p style="font-size:larger;">Deactive Users</p>
            </div>
            <div class="icon">
               <i class="fa fa-user-plus"></i>
            </div>
         </div>   
         <a href="{{ route('users') }}" class="small-box-footer" style="bottom:-52px;">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
   </div>
   @endif
   
   @if(Auth::user()->hasRole('administrator'))
   <div class="col-lg-5 col-5" style="max-width: 25.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
         <div class="row">
            <div class="inner" style="margin-left: 16px;">
               <h3>{{ $attendaceToday }}</h3>
               <p style="font-size:larger;">Checked In</p>
               <?php $p_percent = round(($attendaceToday / $activeUserCount) * 100); ?>
               <h3>{{$p_percent}}%</h3>
            </div>

            <div class="inner" style="margin-left: 20px;">
               <h3>{{ $absentTodayNew }}</h3>
               <p style="text-align:center;font-size:larger;">Non Checked In</p>
               <?php $absent_percent = round(($absentTodayNew / $activeUserCount) * 100); ?>
               <h3>{{$absent_percent}}%</h3>
            </div>
            <div class="icon">
               <i class="fa fa-database"></i>
            </div>
         </div>
      </div>
   </div>
   <div class="col-lg-5 col-5" style="max-width: 38.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
         <div class="row">
            <div class="inner" style="margin-left: 25px;">
               <h3>{{ $areaCount }}</h3>
               <p style="font-size:larger;">Total Areas</p>
            </div>
            <div class="inner" style="margin-left: 25px;">
               <h3>{{ $attendanceAreaCount }}</h3>
               <p style="font-size:larger;">Checked In</p>
               <?php $check_percent = round(($attendanceAreaCount / $areaCount) * 100); ?>
               <h3>{{$check_percent}}%</h3>
            </div>
            <div class="inner" style="margin-left: 25px;">
               <h3>{{ $reaminingAreaCount }}</h3>
               <p style="font-size:larger;">Non Checked In</p>
               <?php $not_check_percent = round(($reaminingAreaCount / $areaCount) * 100); ?>
               <h3>{{$not_check_percent}}%</h3>
            </div>
            
         </div>   
         <div class="icon">
            <i class="fa fa-map-marked-alt"></i>
         </div>
         <a href="{{ route('areas') }}" class="small-box-footer" >More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
   </div>
   @endif
</div>

<div class="row">
   
   <div class="col-lg-5 col-5" style="max-width: 33.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
         <div class="inner">
            <!-- <h3></h3> -->
            <p style="font-size:larger;font-weight: 800;">OBST Monthly Attendance Graph</p>
         </div>
         <!-- <div class="icon">
            <i class="fa fa-user-plus"></i>
         </div> -->
         <a href="#pill1"  class="small-box-footer" style="bottom:-85px;">More info <i class="fas fa-arrow-circle-right" ></i></a>
      </div>
   </div>
   <div class="col-lg-5 col-5" style="max-width: 33.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
         <div class="inner">
            <!-- <h3>{{ $userCount }}</h3> -->
            <p style="font-size:larger;">Employee Not Logged In Till Date</p>
         </div>
         <!-- <div class="icon">
            <i class="fa fa fa-database"></i>
         </div> -->
         <a href="#pill2" class="small-box-footer" style="bottom:-85px;">More info <i class="fas fa-arrow-circle-right" ></i></a>
      </div>
   </div>
   <div class="col-lg-5 col-5" style="max-width: 32.666667%;">
      <!-- small box -->
      <div class="small-box bg-primary" style="height: 180px;">
         <div class="inner">
            <!-- <h3>{{ $userCount }}</h3> -->
            <p style="font-size:larger;">Employee Not Logged In One Week</p>
         </div>
         <!-- <div class="icon">
            <i class="fa fa-map-marked-alt"></i>
         </div> -->
         <a href="#pill3"  class="small-box-footer" style="bottom:-85px;">More info <i class="fas fa-arrow-circle-right" ></i></a>
      </div>
   </div>
</div>
<div class="card-body" id="pill1">
   <div class="card">
      <div class="card-header">
         <h3 class="card-title" style="font-weight: 800;font-size: larger;">OBST Monthly Attendance Graph</h3>
      </div>
   </div>
   <h6><b></b></h6>
   <form action="{{ url('home')}}" method="GET" class="mb-20">
    
      <div class="row">
         <div class="col-md-3" id="role_filter" style="
            align-items: center;margin-top: 8px;">
            <div>
               <label for="role" class="m-0">Year</label>
               @php
               $currentYear = date('Y');
               @endphp

               <select name="year" class="form-control">
                  @for ($year = $currentYear; $year >= $currentYear - 4; $year--)
                     <option value="{{ $year }}" @if (old('year', $years) == $year) selected="selected" @endif>{{ $year }}</option>
                  @endfor
               </select>
            </div>
         </div>
         <div class="col-md-3" id="role_filter" style="
            align-items: center;margin-top: 8px;">
            <div>
               <label for="role" class="m-0">Month</label>
               <select name="month" id="month" class="form-control" >
                  <!-- <option value="-01">Previous Month</option> -->
                  <option value="01" @if (old('month',$month) == '01') selected="selected" @endif>January</option>
                  <option value="02" @if (old('month',$month) == '02') selected="selected" @endif>February</option>
                  <option value="03" @if (old('month',$month) == '03') selected="selected" @endif>March</option>
                  <option value="04" @if (old('month',$month) == '04') selected="selected" @endif>April</option>
                  <option value="05" @if (old('month',$month) == '05') selected="selected" @endif>May</option>
                  <option value="06" @if (old('month',$month) == '06') selected="selected" @endif>June</option>
                  <option value="07" @if (old('month',$month) == '07') selected="selected" @endif>July</option>
                  <option value="08" @if (old('month',$month) == '08') selected="selected" @endif>August</option>
                  <option value="09" @if (old('month',$month) == '09') selected="selected" @endif>September</option>
                  <option value="10" @if (old('month',$month) == '10') selected="selected" @endif>October</option>
                  <option value="11" @if (old('month',$month) == '11') selected="selected" @endif>November</option>
                  <option value="12" @if (old('month',$month) == '12') selected="selected" @endif>December</option>
               </select>
            </div>
         </div>
         <div class="col-md-3" id="role_filter" style="
            align-items: center;margin-top: 8px;">
            <div>
               <label for="role" class="m-0">Branch</label>
               <select name="branch" id="branch" class="form-control" >
                  <!-- <option value="">All</option> -->
                  @foreach ($branchName as $value )
                  <option value="{{$value->id}}" @if (old('month',$branch) == $value->id) selected="selected" @endif>{{$value->name}}-{{$value->address}}</option>
                  @endforeach
               </select>
            </div>
         </div>
         <div class="col-md-3" style="display: flex;
            align-items: center;">
            <div style="margin-top: 30px">
               <!-- <input type="submit" value="Export" class="btn btn-primary"> -->
               <button type="submit" value="submit" class="btn btn-primary" name="submit">Submit</button>
            </div>
         </div>
        
      </div>
   </form>
</div>
<div id="chartdiv"></div>

<hr>
<div class="card" id="pill2">
   <div class="card-header">
      <h3 class="card-title" style="font-weight: 800;font-size: larger;">Employee Not Logged In Till Date</h3>
      <a href="{{url('employ-not-login-report')}}" class="btn btn-primary float-right" name="submit" id="filter" >Download Report <i class="fa fa-download"></i></a>
   </div>
   <div class="card-body">
      <div class="table-responsive">
         <table id="notloginemployee" class="display" style="width:100%">
         <thead>
            <tr>
               <th>Employee Id</th>
               <th>Name</th>
               <th>Role</th>
               <th>Branch Name</th>
               <th>Sole Id</th>
            </tr>
         </thead>
         <tbody>
            @if(!empty($notLoggedInEmployee) && isset($notLoggedInEmployee))
            @foreach($notLoggedInEmployee as $key=>$value)
            <tr>
               <td>{{$value->emp_id ?? "-"}}</td>
               <td>{{$value->name ?? "-"}}</td>
               <td>{{$value['userRole']['display_name'] ?? "-"}}</td>
               <td>{{$value['area']['address'] ?? "-"}}</td>
               <td>{{$value['area']['name'] ?? "-"}}</td>
            </tr>
            @endforeach
            @endif
         </tbody>
         <table>
      </div>
   </div>
</div>
<hr>
<div class="card" id="pill3">
   <div class="card-header">
      <h3 class="card-title" style="font-weight: 800;font-size: larger;">Employee Not Logged In One Week</h3>
      <a href="{{url('employ-not-login-one-week-report')}}" class="btn btn-primary float-right" name="submit" id="filter" >Download Report <i class="fa fa-download"></i></a>
   </div>
   <div class="card-body">
      <div class="table-responsive">
         <table id="example" class="display" style="width:100%">
         <thead>
            <tr>
               <th>Employee Id</th>
               <th>Name</th>
               <th>Role</th>
               <th>Branch Name</th>
               <th>Sole Id</th>
               <th>Last Login Date Time</th>
            </tr>
         </thead>
         <tbody>
            @if(!empty($oneWeekEmployee) && isset($oneWeekEmployee))
            @foreach($oneWeekEmployee as $key=>$value)
            <tr>
               <td>{{$value->emp_id ?? "-"}}</td>
               <td>{{$value->name ?? "-"}}</td>
               <td>{{$value['userRole']['display_name'] ?? "-"}}</td>
               <td>{{$value['area']['address'] ?? "-"}}</td>
               <td>{{$value['area']['name'] ?? "-"}}</td>
               <?php  $userlog= "" ;
                      $userlog = App\Models\UserLog::where('user_id',$value->id)
                                                   ->orderBy('id','desc')->first();
               ?>
               @if(!empty($userlog))
               <td>{{date('d-m-Y',strtotime($userlog['login_date'])) ?? ""}}-{{$userlog['login_time'] ?? ""}}</td>
               @else
               <td>{{"-"}}</td>
               @endif
            </tr>
            @endforeach
            @endif
         </tbody>
         <table>
      </div>
   </div>
</div>
<hr>

<style>
   #chartdiv {
   width: 100%;
   height: 500px;
   }
</style>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script> 
   $(document).ready(function () {
    $('#example').DataTable();
   });
   
   $(document).ready(function () {
    $('#notloginemployee').DataTable();
   });
</script>
<?php
   $present =0;
   $absent=0;
   $todayDate =Null;
   if($result->count()>0){
     // dd($result->count());
     $raw_data=[];
     foreach($result as $key=>$value) {
       
       $date = date('m');
       $year = date('Y');
       // $present =0;
       // $absent=0;
       $todayDate = date('d',strtotime($value->date));
       
        // if(!empty($area) && !empty($value->in_location_id)){
         
           $present = \App\Models\Attendance::where('date',$value->date)->where('status',1)->where('in_location_id',$area)->groupBy('date')->count();
           
           $absent = \App\Models\Attendance::where('date',$value->date)->where('status',5)->where('in_location_id',$area)->groupBy('date')->count();
           
        // }
         // else if($value->date && !empty($value->in_location_id)){
         //   $present = \App\Models\Attendance::where('date',$value->date)->where('in_location_id',$area)->where('status',1)->groupBy('date')->count();
         //   $absent = \App\Models\Attendance::where('date',$value->date)->where('in_location_id',$area)->where('status',5)->groupBy('date')->count();
         
         // }else{
         //   $present = \App\Models\Attendance::where('status',1)->groupBy('date')->count();
         //   $absent = \App\Models\Attendance::where('status',5)->groupBy('date')->count();
         // }
         
         
         // echo "['".$todayDate."', '".$absent."', '".$present."'],";
         $raw_data[] = array('month'=>$todayDate, 'present'=>$present, 'absent'=>$absent);
     }
   }else{
     $raw_data[] = array('month'=>$todayDate, 'present'=>0, 'absent'=>0);
   }
   $r_data = json_encode($raw_data);
   ?>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<style>
.amcharts-chart-div a {display:none !important;}

</style>
<!-- Chart code -->
<script>
   am5.ready(function() {
  
   // Create root element
   // https://www.amcharts.com/docs/v5/getting-started/#Root_element
   var root = am5.Root.new("chartdiv");
   
    /* remove amchart logo */
   root._logo.dispose();
   // Set themes
   // https://www.amcharts.com/docs/v5/concepts/themes/
   root.setThemes([
     am5themes_Animated.new(root)
   ]);
   

   // Create chart
   // https://www.amcharts.com/docs/v5/charts/xy-chart/
   var chart = root.container.children.push(am5xy.XYChart.new(root, {
     panX: false,
     panY: false,
     wheelX: "panX",
     wheelY: "zoomX",
     layout: root.verticalLayout
   }));
   
   
   // Add legend
   // https://www.amcharts.com/docs/v5/charts/xy-chart/legend-xy-series/
   var legend = chart.children.push(
     am5.Legend.new(root, {
       centerX: am5.p50,
       x: am5.p50
     })
   );
   
   var data = <?php echo $r_data; ?>
   
   
   // Create axes
   // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
//    var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
//      categoryField: "month",
//      renderer: am5xy.AxisRendererX.new(root, {
//        cellStartLocation: 0.1,
//        cellEndLocation: 0.9
//      }),
     
//      tooltip: am5.Tooltip.new(root, {})
//    }));
   // Create X-Axis
    var xAxis = chart.xAxes.push(
    am5xy.CategoryAxis.new(root, {
        groupData: true,
        categoryField: "month",
        renderer: am5xy.AxisRendererX.new(root, {
        minGridDistance: 10
        })
    })
    );
   xAxis.data.setAll(data);
   
   var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
     renderer: am5xy.AxisRendererY.new(root, {})
   }));
   
   
   // Add series
   // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
   function makeSeries(name, fieldName) {
     var series = chart.series.push(am5xy.ColumnSeries.new(root, {
       name: name,
       xAxis: xAxis,
       yAxis: yAxis,
       groupData: true,
       valueYField: fieldName,
       minGridDistance: 30,
       categoryXField: "month"
     }));

    
     series.columns.template.setAll({
       tooltipText: "{name} - {valueY}",
       width: am5.percent(50),
       tooltipY: 0
     });
   
     series.data.setAll(data);
   
     // Make stuff animate on load
     // https://www.amcharts.com/docs/v5/concepts/animations/
     series.appear();
   
     series.bullets.push(function () {
       return am5.Bullet.new(root, {
         locationY: 0,
         sprite: am5.Label.new(root, {
           text: "{valueY}",
           fill: root.interfaceColors.get("alternativeText"),
           centerY: 0,
           centerX: am5.p50,
           populateText: true
         })
       });
     });
   
     legend.data.push(series);
   }
   
   makeSeries("Present", "present");
   makeSeries("Absent", "absent");
   
   var scrollbarX = am5xy.XYChartScrollbar.new(root, {
        orientation: "horizontal",
        height: 50
   });

   chart.set("scrollbarX", scrollbarX);
   // Make stuff animate on load
   // https://www.amcharts.com/docs/v5/concepts/animations/
   chart.appear(1000, 100);
   
      yAxis.children.unshift(am5.Label.new(root, {
         text: 'Attendance (Count)',
         textAlign: 'center',
         y: am5.p50,
         rotation: -90,
         fontWeight: 'bold'
      }));

      xAxis.children.push(am5.Label.new(root, {
         text: 'Date',
         textAlign: 'center',
         x: am5.p50,
         fontWeight: 'bold'
      }));
   }); // end am5.ready()

   
</script> 
@stop
@section('css')
<style>
   table {
   font-family: arial, sans-serif;
   border-collapse: collapse;
   width: 100%;
   }
   td, th {
   border: 1px solid #dddddd;
   text-align: left;
   padding: 8px;
   }
</style>
@stop
@section('js')
@stop