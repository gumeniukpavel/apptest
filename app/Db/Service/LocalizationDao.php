<?php

namespace App\Db\Service;

class LocalizationDao
{
    public function getFile($locale)
    {
        $filepath = resource_path('lang/'.$locale).'/frontend.php';
        return is_file($filepath) ? include $filepath : null;
    }
}
