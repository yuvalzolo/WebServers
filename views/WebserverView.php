<?php

namespace views;

class WebserverView
{
    public function render($data)
    {
        // Render the view or format the data in a json format
        return json_encode($data);
    }
}
