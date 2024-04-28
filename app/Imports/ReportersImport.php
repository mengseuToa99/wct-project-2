<?php

namespace App\Imports;

use App\Models\Reporter;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReportersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $rows = [];

public function model(array $row)
{
    $emailParts = explode('@', $row['email']);
    $username = $emailParts[0]; // Use the part before '@' as the username
    
    $password = 'password'; 

    $reporter = new Reporter([
        'username' => $username,
        'email' => $row['email'],
        'password' => Hash::make($password), // Use a default password
        'role' => $row['role'],
    ]);
    $reporter->save(); 

    Mail::to($reporter->email)->send(new ResetPassword($reporter, $password));

    $this->rows[] = $reporter->toArray();

    return $reporter;
}

public function getRows(): array
{
    return $this->rows;
}
}
