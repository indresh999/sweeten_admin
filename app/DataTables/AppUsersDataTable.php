<?php

namespace App\DataTables;

use App\Models\AppUser;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AppUsersDataTable extends DataTable
{

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function($query) {
                return $query->status ?? '-';
            })
            ->editColumn('gender', function($query) {
                return $query->gender ?? '-';
            })
            ->editColumn('status', function($query) {
                $status = 'warning';
                switch ($query->status) {
                    case 'active':
                        $status = 'primary';
                        break;
                    case 'inactive':
                        $status = 'danger';
                        break;
                    case 'banned':
                        $status = 'dark';
                        break;
                }
                return '<span class="text-capitalize badge bg-'.$status.'">'.$query->status.'</span>';
            })
            ->editColumn('created_at', function($query) {
                return date('Y/m/d',strtotime($query->created_at));
            })
            ->filterColumn('full_name', function($query, $keyword) {
                $sql = "CONCAT(users.name,' ',users.name)  like ?";
                return $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('mobile', function($query, $keyword) {
                return $query->orWhereHas('mobile', function($q) use($keyword) {
                    $q->where('mobile', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('city', function($query, $keyword) {
                return $query->orWhereHas('city', function($q) use($keyword) {
                    $q->where('city', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('action', 'users.action')
            ->rawColumns(['action','status']);
    }

    public function query()
    {
        $model = AppUser::query();
        return $this->applyScopes($model);
    }

 
    public function html()
    {
        return $this->builder()
                    ->setTableId('dataTable')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('<"row align-items-center"<"col-md-2" l><"col-md-6" B><"col-md-4"f>><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" i><"col-md-6" p>><"clear">')

                    ->parameters([
                        "processing" => true,
                        "autoWidth" => false,
                    ]);
    }

  
    protected function getColumns()
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'id'],
            ['data' => 'name', 'name' => 'name', 'title' => 'FULL NAME', 'orderable' => false],
            ['data' => 'mobile', 'name' => 'mobile', 'title' => 'Phone Number'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
            ['data' => 'city', 'name' => 'city', 'title' => 'Email'],
            ['data' => 'state', 'name' => 'state', 'title' => 'Email'],
            ['data' => 'pincode', 'name' => 'pincode', 'title' => 'Email'],
            ['data' => 'address', 'name' => 'address', 'title' => 'Address'],
            ['data' => 'lat', 'name' => 'lat', 'title' => 'Lat'],
            ['data' => 'lng', 'name' => 'lng', 'title' => 'Lng'],
            ['data' => 'gender', 'gender' => 'email', 'title' => 'Gender'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Join Date'],
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->searchable(false)
                  ->width(60)
                  ->addClass('text-center hide-search'),
        ];
    }

}
