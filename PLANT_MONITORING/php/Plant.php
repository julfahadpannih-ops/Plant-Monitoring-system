<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../frontend/login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IOT PLANT | Professional UI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="../frontend/js/tailwind.config.js"></script>

    <link rel="stylesheet" href="../frontend/css/dashboard.css">
</head>
<body class="p-3 md:p-4 min-h-screen lg:h-screen lg:overflow-hidden overflow-x-hidden d-flex flex-column">

<div class="container-fluid max-w-[1400px] mx-auto h-full min-h-full d-flex flex-column gap-3 lg:gap-4">

    <div class="row g-3 lg:g-2 flex-grow-1 min-h-0">
        <div class="col-lg-6 h-auto lg:h-100">
            <div class="bg-panel-light rounded-4xl p-3 md:p-4 h-100 soft-shadow d-flex flex-column relative">
                <div class="d-flex justify-content-between align-items-center mb-3 px-2 flex-wrap gap-2">
                    <h1 class="text-lg md:text-xl font-bold tracking-wide m-0"><i class="fa-solid fa-leaf text-accent-green mr-2"></i>IOT SOIL MONITORING</h1>
                    
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-xs font-medium text-text-muted bg-panel-dark px-3 py-1 rounded-full inner-shadow">Ibmar Guinanas | BSIS IV</span>
                        <a href="logout.php" class="btn btn-sm bg-accent-orange text-white border-0 rounded-circle w-8 h-8 d-flex align-items-center justify-content-center hover-lift soft-shadow transition-colors hover:bg-red-600" title="Logout">
        <i class="fa-solid fa-right-from-bracket text-xs"></i>
    </a>
                        <button onclick="toggleRecordPanel()" class="btn btn-sm bg-panel-dark text-text-main border-0 rounded-circle w-8 h-8 d-flex align-items-center justify-content-center hover-lift soft-shadow transition-colors hover:bg-widget-brown hover:text-white" title="View Records">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex-grow-1 rounded-3xl overflow-hidden relative min-h-[200px] lg:min-h-[120px]">
                    <img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1000&auto=format&fit=crop" alt="Farm Field" class="w-100 h-100 object-cover absolute top-0 left-0 transition-transform duration-700 hover:scale-105">
                    <div class="absolute bottom-3 left-3 bg-white/80 backdrop-blur-md px-3 py-1.5 rounded-2xl text-xs font-bold shadow-lg transition-all">
                        <i id="connection-icon" class="fa-solid fa-circle text-warning mr-1"></i> <span id="api-status">Connecting...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 h-auto lg:h-100">
            <div class="bg-panel-light rounded-4xl p-4 h-100 soft-shadow">
                <div class="row h-100 g-3 lg:g-0">
                    <div class="col-md-7 d-flex flex-column h-auto lg:h-100 min-h-[250px] lg:min-h-0">
                        <h3 class="text-base font-bold mb-3">System Log</h3>
                        
                        <ul id="main-log-container" class="space-y-3 flex-grow-1 overflow-y-auto pr-2 min-h-0">
                            <li class="d-flex align-items-start gap-3 transition-all hover:bg-white/30 p-2 rounded-xl">
                                <div class="bg-accent-green text-white rounded-full w-8 h-8 flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-xs"></i></div>
                                <div>
                                    <p class="font-semibold text-sm m-0">System Initialized</p>
                                    <p class="text-xs text-text-muted m-0">Waiting for database sync...</p>
                                </div>
                            </li>
                        </ul>

                        <div class="mt-auto pt-3 border-t border-panel-dark shrink-0">
                            <div class="d-flex justify-content-between align-items-end h-16 mt-2">
                                <div class="bar light h-1/2"></div>
                                <div class="bar bg-accent-green h-3/4"></div>
                                <div class="bar light h-1/3"></div>
                                <div class="bar orange h-full"></div>
                                <div class="bar bg-accent-green h-5/6"></div>
                                <div class="bar orange h-1/2"></div>
                                <div class="bar bg-accent-green h-full"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 text-[10px] text-text-muted font-bold px-1">
                                <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 d-flex flex-column align-items-center justify-content-center border-t lg:border-t-0 lg:border-l border-panel-dark h-auto lg:h-100 pt-4 lg:pt-0">
                        <h3 class="text-base font-bold w-100 text-center lg:text-left lg:pl-3 mb-3">Local Time</h3>
                        <div class="bg-white rounded-full w-36 h-36 soft-shadow border-4 border-panel-light d-flex flex-column align-items-center justify-content-center relative transition-transform duration-500 hover:scale-105 hover:shadow-lg">
                            <span id="clock" class="text-2xl font-bold text-accent-green">00:00</span>
                            <span class="text-[11px] text-text-muted mt-1 font-semibold text-center leading-tight">Siocon<br>Z,N,D</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 lg:g-2 flex-shrink-0">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-panel-dark rounded-3xl p-4 soft-shadow h-100 min-h-[140px] d-flex flex-column hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Temperature</span>
                    <i class="fa-solid fa-temperature-half text-accent-orange bg-panel-light p-2 rounded-full text-sm"></i>
                </div>
                <div class="mt-auto">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter"><span id="temp-val">--</span><span class="text-sm text-text-muted">°C</span></h2>
                    <div class="w-100 bg-panel-light rounded-full h-2 mt-2 inner-shadow"><div class="bg-accent-orange h-2 rounded-full transition-all duration-1000" style="width: 60%"></div></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-widget-brown rounded-3xl p-4 soft-shadow h-100 min-h-[140px] d-flex flex-column text-white hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-panel-light">Air Humidity</span>
                    <i class="fa-solid fa-cloud-rain text-white bg-text-main/20 p-2 rounded-full text-sm"></i>
                </div>
                <div class="mt-auto">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter"><span id="hum-val">--</span><span class="text-sm text-panel-light">%</span></h2>
                    <div class="w-100 bg-text-main/20 rounded-full h-2 mt-2"><div class="bg-white h-2 rounded-full transition-all duration-1000" style="width: 75%"></div></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-panel-light rounded-3xl p-4 soft-shadow h-100 min-h-[140px] d-flex flex-column border-b-4 border-accent-green hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Soil Moisture</span>
                    <i class="fa-solid fa-seedling text-accent-green bg-white p-2 rounded-full shadow-sm text-sm"></i>
                </div>
                <div class="mt-auto d-flex align-items-baseline gap-2">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter"><span id="soil-val">--</span><span class="text-sm text-text-muted">%</span></h2>
                    <span class="text-[10px] font-bold text-accent-green bg-accent-green/10 px-2 py-1 rounded animate-pulse">Live</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="bg-panel-light rounded-3xl p-4 soft-shadow h-100 min-h-[140px] d-flex flex-column hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Irrigation</span>
                    <span id="pump-status-text" class="text-xs font-bold text-text-muted bg-panel-dark px-2 py-1 rounded transition-colors duration-300">OFF</span>
                </div>
                
                <div class="mt-auto d-flex gap-2">
                    <button onclick="togglePump(1)" class="btn flex-grow-1 rounded-2xl bg-accent-green text-white text-sm font-bold py-2.5 hover:bg-[#4a6643] active:scale-95 transition-all border-0 shadow-sm">
                        <i class="fa-solid fa-power-off"></i> ON
                    </button>
                    <button onclick="togglePump(0)" class="btn flex-grow-1 rounded-2xl bg-panel-dark text-text-main text-sm font-bold py-2.5 hover:bg-[#bda683] active:scale-95 transition-all border-0 shadow-sm">
                        OFF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 lg:g-2 flex-shrink-0">
        <div class="col-12 col-md-4">
            <div class="bg-panel-light rounded-3xl p-4 soft-shadow h-100 min-h-[120px] d-flex flex-column border-b-4 border-accent-green hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Nitrogen (N)</span>
                    <i class="fa-solid fa-flask text-accent-green bg-white p-2 rounded-full shadow-sm text-sm"></i>
                </div>
                <div class="mt-auto d-flex align-items-baseline gap-2">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter transition-all duration-300"><span id="n-val">--</span><span class="text-sm text-text-muted">mg/kg</span></h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-panel-light rounded-3xl p-4 soft-shadow h-100 min-h-[120px] d-flex flex-column border-b-4 border-accent-orange hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Phosphorus (P)</span>
                    <i class="fa-solid fa-flask text-accent-orange bg-white p-2 rounded-full shadow-sm text-sm"></i>
                </div>
                <div class="mt-auto d-flex align-items-baseline gap-2">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter transition-all duration-300"><span id="p-val">--</span><span class="text-sm text-text-muted">mg/kg</span></h2>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-panel-light rounded-3xl p-4 soft-shadow h-100 min-h-[120px] d-flex flex-column border-b-4 border-widget-brown hover-lift">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-sm font-bold text-text-main">Potassium (K)</span>
                    <i class="fa-solid fa-flask text-widget-brown bg-white p-2 rounded-full shadow-sm text-sm"></i>
                </div>
                <div class="mt-auto d-flex align-items-baseline gap-2">
                    <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tighter transition-all duration-300"><span id="k-val">--</span><span class="text-sm text-text-muted">mg/kg</span></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="record-backdrop" onclick="toggleRecordPanel()" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300"></div>

<div id="record-panel" class="fixed top-0 right-0 h-full w-full sm:w-96 bg-panel-light shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 flex flex-col rounded-l-4xl border-l border-panel-dark">
    
    <div class="p-4 d-flex justify-content-between align-items-center border-b border-panel-dark">
        <h2 class="text-lg font-bold text-text-main m-0"><i class="fa-solid fa-clock-rotate-left text-accent-green mr-2"></i>System Records</h2>
        <button onclick="toggleRecordPanel()" class="btn bg-panel-dark text-text-main rounded-circle w-8 h-8 d-flex align-items-center justify-content-center hover:bg-widget-brown hover:text-white transition-colors border-0 p-0 soft-shadow">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div id="records-container" class="flex-grow p-4 overflow-y-auto space-y-3">
        </div>

    <div class="p-4 border-t border-panel-dark text-center">
        <button onclick="fetchLogsFromDB()" class="btn w-100 bg-widget-brown text-white rounded-2xl font-bold text-sm py-2 hover:bg-[#9a7e58] transition-colors soft-shadow">
            <i class="fa-solid fa-rotate-right mr-1"></i> Refresh Records
        </button>
    </div>
</div>

<button onclick="toggleChat()" class="fixed bottom-6 right-6 w-14 h-14 bg-accent-green text-white rounded-full shadow-lg hover-lift flex items-center justify-center z-[60] text-2xl transition-colors hover:bg-[#4a6643] border-4 border-white">
    <i class="fa-solid fa-robot"></i>
</button>

<div id="chat-widget" class="fixed bottom-24 right-4 md:right-6 w-[350px] max-w-[calc(100vw-2rem)] bg-panel-light rounded-3xl shadow-2xl z-[60] flex flex-col overflow-hidden border-2 border-white" style="height: 500px; max-height: 70vh;">
    <div class="bg-accent-green text-white p-4 flex justify-between items-center soft-shadow relative z-10">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-full w-10 h-10 flex items-center justify-center backdrop-blur-sm shadow-inner">
                <i class="fa-solid fa-seedling text-white text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm m-0 leading-tight tracking-wide">AgriBot Assistant</h3>
                <span class="text-[10px] text-white/90 flex items-center gap-1 font-medium"><i class="fa-solid fa-circle text-[8px] text-green-300 animate-pulse"></i> System Active</span>
            </div>
        </div>
        <button onclick="toggleChat()" class="text-white/80 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10">
            <i class="fa-solid fa-chevron-down"></i>
        </button>
    </div>
    
    <div id="chat-messages" class="flex-grow p-4 overflow-y-auto bg-[#e5d4be]/30 flex flex-col gap-3 scroll-smooth">
        <div class="flex justify-start log-item-animate">
            <div class="chat-bubble-ai px-4 py-3 rounded-2xl text-sm shadow-sm max-w-[85%] border border-panel-dark/30">
                Hello! 🌿 I am AgriBot, your smart monitoring assistant. <br><br>You can ask me about: <br>• <b>Status</b> (Temp, Soil, NPK) <br>• <b>Ping/Signal</b> <br>• <b>History logs</b>
            </div>
        </div>
    </div>

    <div class="p-3 bg-white border-t border-panel-dark flex gap-2 items-center">
        <input type="text" id="chat-input" onkeypress="handleChatKeyPress(event)" placeholder="Ask me something..." autocomplete="off" class="flex-grow bg-panel-light/40 border border-panel-dark rounded-full px-4 py-2 text-sm focus:outline-none focus:border-accent-green focus:ring-1 focus:ring-accent-green transition-all text-text-main placeholder:text-text-muted">
        <button onclick="sendChatMessage()" class="bg-widget-brown text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#9a7e58] transition-transform active:scale-95 shadow-sm shrink-0">
            <i class="fa-solid fa-paper-plane text-xs relative -left-0.5"></i>
        </button>
    </div>
</div>

<script src="../frontend/js/dashboard.js"></script>
</body>
</html>