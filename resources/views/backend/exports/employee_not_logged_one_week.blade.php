<table>
    <thead>
        <tr>


            <th>S.No.</th>
            <th>Emp Code</th>
            <th>Emp Name</th>
            <th>Role</th>
            <th>Branch Name</th>
            <th>Sole Id</th>
            <th>Login Date Time</th>
        </tr>
    </thead>
    <tbody>
        
        @foreach($attdence as $key=>$values)
      
        <tr>
            <td>{{ $key+1 }}</td>      
            <td>{{ $values->emp_id ?? "-" }}</td>            
            <td>{{ $values->name ?? "-" }}</td>
            <td>{{ !empty($values['userRole']['display_name']) ? $values['userRole']['display_name']  : "-" }}</td>
            <td>{{ !empty($values['area']['address']) ? $values['area']['address']  : "-" }}</td>
            <td>{{ !empty($values['area']['name']) ? $values['area']['name']  : "-" }}</td>
            <td>{{ !empty($values['userLog']['login_date']) ?? ""  }} - {{ !empty($values['userLog']['login_time']) ?? ""}}</td>
        </tr>
        @endforeach
    <tr></tr>
    </tbody>
</table>