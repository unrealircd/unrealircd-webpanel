<?php
require_once "../common.php";
require_once "../connection.php";
require_once "../header.php";
require_once "../Classes/class-checkup.php";

$checkup = new CheckUp();

?>

<h4>Network Health Checkup</h4>

<div class="container">

<div class="row mt-3">
    <div class="col-sm mb-3">
        <div class="card text-center">
            <div class="card-header bg-<?php echo ($checkup->num_of_problems['chanmodes']) ? "danger" : "success"; ?> text-white">
                <div class="row">
                    <div class="col">
                        <i class="fa fa-hashtag fa-3x"></i>
                    </div>
                    <div class="col">
                        <h3 class="display-4"><?php echo $checkup->num_of_problems['chanmodes']; ?></h3><div class="display-5">problems</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h6>Channel Modes</h6>
                    </div>
                    <div class="col"> <a class="btn btn-primary">View</a></div>
                </div>
            </div>
        </div>
        

    </div>
    <div class="col-sm mb-3">
        <div class="card text-center">
            <div class="card-header bg-<?php echo ($checkup->num_of_problems['usermodes']) ? "danger" : "success"; ?> text-white">
                <div class="row">
                    <div class="col">
                        <i class="fa fa-user fa-3x"></i>
                    </div>
                    <div class="col">
                        <h3 class="display-4"><?php echo $checkup->num_of_problems['usermodes']; ?></h3><div class="display-5">problems</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h6>User Modes</h6>
                    </div>
                    <div class="col"><a class="btn btn-primary">View</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm mb-3">
        <div class="card text-center">
            <div class="card-header bg-warning">
                <div class="row">
                    <div class="col">
                        <i class="fa fa-plug fa-3x"></i>
                    </div>
                    <div class="col">
                        <h3 class="display-4"><?php // nothing ?></h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h6>Modules</h6>
                    </div>
                    <div class="col"><a class="btn btn-primary">View</a></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm mb-3">
        <div class="card text-center">
            <div class="card-header bg-secondary text-white">
                <div class="row">
                    <div class="col">
                        <i class="fa fa-network-wired fa-3x"></i>
                    </div>
                    <div class="col">
                        <h3 class="display-4"><?php // nothing ?></h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h6>Servers</h6>
                    </div>
                    <div class="col"> <a class="btn btn-primary">View</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<body>
<canvas id="myChart" style="width:100%;max-width:400px"></canvas>

<script id="js">

var xValues = ["15 minutes", "5 minutes", "1 minute"];
var yValues = [<?php echo "\"".$cpuUsage[2]."\", \"".$cpuUsage[1]."\", \"".$cpuUsage[0]."\""; ?>];
var barColors = ["blue", "blue", "blue"]

new Chart("myChart", {
  type: "bar",
  type: "bar",
  data: {
    labels: xValues,
    datasets: [{
      backgroundColor: barColors,
      data: yValues
    }]
  },

  options: {
    legend: {display: false},
    title: {
      display: true,
      text: "CPU Usage",
      fontSize: 16
    },
    scales: {
      xAxes: [{ticks: {min: 0, max:15}}],
      yAxes: [{ticks: {min: 0, max:1}}],
    }
  }
});
</script>
<div id="stats-container">
    <p id="cpu-usage">CPU Usage: </p>
    <p id="memory-usage">Memory Usage: </p>
</div>

<script>
    function updateStats() {
        var xhttp = new XMLHttpRequest();
        var BASE_URL = "<?php echo BASE_URL; ?>";
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data = JSON.parse(this.responseText);
                document.getElementById("cpu-usage").innerHTML = "Current Usage: <code>" + data.cpu + "</code>";
                document.getElementById("memory-usage").innerHTML = "Memory Usage: <code>" + data.memory + "</code>";
            }
        };
        xhttp.open("GET", BASE_URL + "api/data.php", true);
        xhttp.send();
    }
    updateStats();
    setInterval(updateStats, 1000); // Update stats every second
</script>
