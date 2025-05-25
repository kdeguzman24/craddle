<?php
session_start();
require_once "config.php";

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$sound = $temperature = $humidity = "N/A";
$baby_status = "Unknown";
$movement_status = "Unknown";

$sensor_query = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$sensor_result = $mysqli->query($sensor_query);

if ($sensor_result && $sensor_result->num_rows > 0) {
    $sensor_row = $sensor_result->fetch_assoc();
    $sound = $sensor_row['sound'];
    $temperature = $sensor_row['temperature'];
    $humidity = $sensor_row['humidity'];

    if (is_numeric($sound)) {
        $baby_status = ((float)$sound > 300) ? "Crying" : "Calm";
    }

    if (isset($sensor_row['movement'])) {
        $movement_status = ($sensor_row['movement'] == 1) ? "Moving" : "Still";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DreamNest Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(to right, #3c0b76, #1b3a73);
            color: #fff;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: rgba(0, 0, 0, 0.2);
            flex-wrap: wrap;
            height: 60px;
        }

        .branding {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .branding img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 600;
        }

        #clock {
            font-size: 22px;
            font-weight: 600;
        }

        .nav-links a {
            margin-left: 15px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #ddd;
        }

        .main {
            padding: 30px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 16px;
        }

        .video-card img {
            width: 100%;
            max-height: 350px;
            object-fit: cover;
            border-radius: 15px;
        }

        .sensor-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
            margin-top: 20px;
        }

        .sensor-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .sensor-item .label {
            font-size: 14px;
            color: #ccc;
        }

        .sensor-item .value {
            font-size: 22px;
            font-weight: bold;
        }

        /* 🔄 Improved toggle switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 64px;
            height: 36px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            backdrop-filter: blur(8px);
            box-shadow: inset 0 0 8px rgba(255, 255, 255, 0.2);
            transition: 0.4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 28px;
            width: 28px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            border-radius: 50%;
            transition: 0.4s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        input:checked + .slider {
            background-color: rgba(102, 187, 106, 0.4);
            box-shadow: 0 0 12px rgba(102, 187, 106, 0.8);
        }

        input:checked + .slider:before {
            transform: translateX(28px);
        }

        .toggle-wrapper {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .toggle-label {
            margin-top: 10px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .sensor-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <div class="branding">
            <img src="DreamNestLogo.png" alt="DreamNest Logo">
            <span class="brand-title">DreamNest</span>
        </div>
        <p id="clock"></p>
        <div class="nav-links">
            <a href="records.php">Records</a>
            <a href="settings.php">Settings</a>
            <a href="db.php?logout=true" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="dashboard">
            <div class="card video-card">
                <h3>Live Video Stream</h3>
                <img id="live-stream" src="http://192.168.68.129:81/stream" alt="Live ESP32-CAM baby monitor stream">
                <p id="error-message" style="display: none;">Failed to load stream.</p>
            </div>

            <div class="card">
                <h3>Sensor Overview</h3>
                <div class="sensor-grid">
                    <div class="sensor-item"><span class="label">Sound</span><span class="value" id="sound-status"><?php echo htmlspecialchars($sound); ?></span></div>
                    <div class="sensor-item"><span class="label">Temperature (°C)</span><span class="value" id="temperature"><?php echo htmlspecialchars($temperature); ?></span></div>
                    <div class="sensor-item"><span class="label">Humidity (%)</span><span class="value" id="humidity"><?php echo htmlspecialchars($humidity); ?></span></div>
                    <div class="sensor-item"><span class="label">Baby Status</span><span class="value" id="baby-status"><?php echo htmlspecialchars($baby_status); ?></span></div>
                    <div class="sensor-item full-width-center"><span class="label">Movement Status</span><span class="value" id="movement-status"><?php echo htmlspecialchars($movement_status); ?></span></div>
                </div>
                <div class="toggle-wrapper">
                    <label class="switch">
                        <input type="checkbox" id="toy-control" onchange="toggleToy(this.checked)">
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Swing</span>
                </div>
            </div>

            <div class="card">
                <h3>Peak Crying Hours</h3>
                <canvas id="cryingChart" width="400" height="400"></canvas>
            </div>

            <div class="card">
                <h3>High Temperature (>30°C)</h3>
                <canvas id="tempChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            fetchToyStatus();
            setInterval(fetchToyStatus, 1000);
        });

        function toggleToy(isChecked) {
            fetch("update_toy_status.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: "status=" + (isChecked ? "on" : "off")
            });
        }

        function fetchToyStatus() {
            fetch('toy_status.txt?ts=' + new Date().getTime())
                .then(response => response.text())
                .then(status => {
                    document.getElementById("toy-control").checked = status.trim() === "on";
                });
        }

        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-GB');
        }
        setInterval(updateClock, 1000);
        updateClock();

        function fetchData() {
            fetch('latest_data.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('sound-status').textContent = data.sound;
                    document.getElementById('temperature').textContent = data.temperature;
                    document.getElementById('humidity').textContent = data.humidity;
                    document.getElementById('baby-status').textContent = data.baby_status;
                    document.getElementById('movement-status').textContent = data.movement_status;
                });
        }

        
        function loadCryingChart() {
            fetch('chart_data.php')
                .then(response => response.json())
                .then(data => {
                    const hours = [...Array(24).keys()].map(h => `${h}:00`);
                    const cryingData = data.map(d => d.crying);
                    const ctx = document.getElementById('cryingChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: hours,
                            datasets: [{
                                label: 'Crying Events',
                                data: cryingData,
                                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#fff'
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: '#fff'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Number of Crying Events',
                                        color: '#fff'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#fff'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Hour of Day',
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error("Chart data error:", error));
        }

        function loadTempChart() {
            fetch('chart_data.php')
                .then(response => response.json())
                .then(data => {
                    const hours = [...Array(24).keys()].map(h => `${h}:00`);
                    const hotData = data.map(d => d.hot);
                    const ctx = document.getElementById('tempChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: hours,
                            datasets: [{
                                label: 'Temperature > 30°C',
                                data: hotData,
                                borderColor: 'rgba(255, 206, 86, 1)',
                                backgroundColor: 'rgba(255, 206, 86, 0.3)',
                                fill: false,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#fff'
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    min: 0,
                                    max: 50,
                                    ticks: {
                                        stepSize: 10,
                                        color: '#fff'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Number of High Temp Events',
                                        color: '#fff'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#fff'
                                    },
                                    title: {
                                        display: true,
                                        text: 'Hour of Day',
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error("Temp chart data error:", error));
        }

        fetchData();
        setInterval(fetchData, 1000);
        loadCryingChart();
        loadTempChart();
    </script>
</body>
</html>
