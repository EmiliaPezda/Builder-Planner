<?php

namespace PlannerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PlannerBundle extends Bundle
{
    public function ajaxAction(Request $request)
    {
        $data = $request->request->get('request');
    }
}
