<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Response;

final class CountdownController
{
    public function page(): never
    {
        Response::view('countdown');
    }
}