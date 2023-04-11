<?php
require_once "../../common.php";
require_once "../../connection.php";
require_once "../../header.php";
require_once "../../footer.php";
GLOBAL $rpc;
/* Get the user list */
$users = $rpc->user()->getAll();
$userFlags = array();
foreach($users as $user)
{
    if (isset($user->geoip->country_code))
    array_push($userFlags, $user->geoip->country_code);
}
$userFlags = array_count_values($userFlags);

arsort($userFlags);

$li = "";

foreach($userFlags as $country_code => $count){
    $li .= '<li>
    <div class="drag"><img src="https://flagcdn.com/108x81/'.htmlspecialchars(strtolower($country_code)).'.png" width="108" height="81"><br />
    '.$country_code . '
    </div>
    <div class="count">' . $count . ' <span>users</span></div>
    </li>';
}

$html = <<<HTML
<style>
    #Top-countries ul {
	list-style: none;
}

#Top-countries li {
  padding: 5px;
  display: inline-block;
  margin: 9px;
  border: solid 1px #ccc;
}

#Top-countries li .drag {
  text-align: center;
}

#Top-countries li .count {
  text-align: center;
  font-size: 2.2rem;
}

#Top-countries li .count span {
  display:block;
  text-align: center;
  font-size: 1rem;
}

</style>
<div id="main_contain" class="container-fluid" style="padding-left: 210px" role="main">
    <h4>Top Countries</h4>
    <div id="Top-countries">
        <ul>
            $li
        </ul>
    </div>

</div>
</div>
HTML;

echo $html;
