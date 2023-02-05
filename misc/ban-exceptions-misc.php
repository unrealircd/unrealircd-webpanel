<?php


function convert_exceptiontypes_to_badges($types)
{
    $badges = "";
    var_dump($types);
    for ($i = 0; $i <= strlen($types) - 1; $i++)
    {
        if ($types[$i] == "k")
            $name = "K-Line";
        if ($types[$i] == "G")
            $name = "G-Line";
        elseif ($types[$i] == "z")
            $name = "Z-Line";
        elseif ($types[$i] == "Z")
            $name = "GZ-Line";
        elseif ($types[$i] == "Q")
            $name = "Q-Line";
        elseif ($types[$i] == "s")
            $name = "Shun";
        elseif ($types[$i] == "F")
            $name = "Spamfilter";
        elseif ($types[$i] == "b")
            $name = "Blacklist";
        elseif ($types[$i] == "c")
            $name = "Connect Flood";
        elseif ($types[$i] == "d")
            $name = "Handshake";
        elseif ($types[$i] == "m")
            $name = "Max Per IP";
        elseif ($types[$i] == "r")
            $name = "Anti-Random";
        elseif ($types[$i] == "8")
            $name = "Anti-Mixed-UTF8";
        elseif ($types[$i] == "v")
            $name = "Versions";
        if (isset($name))
            $badges .= "<span class=\"rounded-pill badge badge-info\">$name</span>";
    }
    return $badges;
}
