<?php
// Logout functionality
require 'config.php';

if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamNest Summary</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

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
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.2);
            flex-wrap: wrap;
            gap: 10px;
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
            font-size: 24px;
            font-weight: 600;
        }

        #clock {
            font-size: 20px;
            font-weight: 600;
            min-width: 110px;
            text-align: center;
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

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px 40px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        header h1 {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 40px;
        }

        .summary-wrapper {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .summary-section {
            flex: 1 1 500px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .summary-section.placeholder {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            min-height: 100%;
            padding: 30px;
            color: #ccc;
            font-size: 18px;
            font-style: italic;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .summary-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 30px 20px;
            border-radius: 14px;
            text-align: center;
            color: #fff;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 160px;
        }

        .summary-box h2 {
            font-size: 48px;
            margin: 0 0 12px 0;
            line-height: 1;
        }

        .summary-box p {
            font-size: 18px;
            margin: 0;
            opacity: 0.85;
            font-weight: 600;
        }

        .record-list-title {
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: bold;
            color: #fff;
        }

        #record-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 14px;
        }

        .record-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 15px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
        }

        @media (max-width: 1000px) {
            .summary-wrapper {
                flex-direction: column;
            }

            .container {
                padding: 20px;
            }

            header h1 {
                font-size: 22px;
                margin-bottom: 30px;
            }

            .summary-box h2 {
                font-size: 38px;
            }

            .summary-box {
                min-height: 140px;
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="branding">
            <img src="DreamNestLogo.png" alt="DreamNest Logo">
            <span class="brand-title">DreamNest</span>
        </div>
        <p id="clock"></p>
        <div class="nav-links">
            <a href="db.php">Dashboard</a>
            <a href="settings.php">Settings</a>
            <a href="records.php?logout=true" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <header>
            <h1>Baby Activity Summary</h1>
        </header>

        <div class="summary-wrapper">
            <!-- Left Summary Section -->
            <div class="summary-section" id="summary-section">
                <div class="summary-box"><h2>-</h2><p>Total Calm Periods</p></div>
                <div class="summary-box"><h2>-</h2><p>Total Crying Instances</p></div>
                <div class="summary-box"><h2>-</h2><p>Average Temperature (°C)</p></div>
                <div class="summary-box"><h2>-</h2><p>Average Humidity (%)</p></div>
                <div class="summary-box"><h2>-</h2><p>Average Movement</p></div>
                <div class="summary-box"><h2>-</h2><p>Last Activity</p></div>
            </div>

            <!-- Right Records Section -->
            <div class="summary-section placeholder">
                <div class="record-list-title">Recent Baby Status</div>
                <div id="record-list">
                    <p>Loading records...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-GB');
        }

        setInterval(updateClock, 1000);
        updateClock();

        function fetchSummary() {
            fetch('summary_data.php')
                .then(response => response.json())
                .then(data => {
                    const boxes = document.querySelectorAll('.summary-box h2');
                    boxes[0].textContent = data.calm_count || 0;
                    boxes[1].textContent = data.crying_count || 0;
                    boxes[2].textContent = data.avg_temp ? parseFloat(data.avg_temp).toFixed(1) + '°C' : '-';
                    boxes[3].textContent = data.avg_hum ? parseFloat(data.avg_hum).toFixed(1) + '%' : '-';
                    boxes[4].textContent = data.avg_movement ? parseFloat(data.avg_movement).toFixed(2) : '-';
                    boxes[5].textContent = data.latest_entry ? data.latest_entry : 'N/A';
                })
                .catch(error => {
                    console.error('Error fetching summary:', error);
                });
        }  

        function fetchRecords() {
            fetch('summary_records.php')
                .then(response => response.json())
                .then(records => {
                    const list = document.getElementById('record-list');
                    list.innerHTML = ''; // Clear previous records

                    if (records.length === 0) {
                        list.innerHTML = '<p>No recent records found.</p>';
                        return;
                    }

                    records.forEach(rec => {
                        const item = document.createElement('div');
                        item.className = 'record-item';
                       /*  item.innerHTML = `
                            <strong>${new Date(rec.timestamp).toLocaleString()}</strong><br>
                            Status: ${rec.baby_status} <br>
                            Temp: ${parseFloat(rec.temperature).toFixed(1)}°C, 
                            Humidity: ${parseFloat(rec.humidity).toFixed(1)}%, 
                            Sound: ${rec.sound_level}, 
                            Motion: ${rec.motion}
                        `; */
                        item.innerHTML = `
                            <strong>${new Date(rec.timestamp).toLocaleString()}</strong><br>
                            Status: ${rec.baby_status} <br>        
                            Sound: ${rec.sound}, 
                            Motion: ${rec.movement_status}
                        `;
                        list.appendChild(item);
                    });
                })
                .catch(err => {
                    console.error('Error fetching records:', err);
                    document.getElementById('record-list').innerHTML = '<p>Error loading records.</p>';
                });
        }

        fetchSummary();
        fetchRecords();
        setInterval(fetchSummary, 5000);
        setInterval(fetchRecords, 5000);
    </script>
</body>

</html>
