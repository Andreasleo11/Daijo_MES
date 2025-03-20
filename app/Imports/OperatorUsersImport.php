<?php

namespace App\Imports;

use App\Models\OperatorUser;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OperatorUsersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row);
        return new OperatorUser([
            'name'            => $row['name'],
            'password'        => Str::random(10),// Default password
            'profile_picture' => null,
            'department'      => $row['dept'],
            'position'        => $row['position'],
        ]);
    }
}
