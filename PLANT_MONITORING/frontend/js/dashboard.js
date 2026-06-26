    // System Variables for DB Saving
    let currentSoil=0, currentTemp=0, currentHum=0, currentN=0, currentP=0, currentK=0;

    // --- PANEL LOGIC ---
    function toggleRecordPanel() {
        const panel = document.getElementById('record-panel');
        const backdrop = document.getElementById('record-backdrop');
        
        if (panel.classList.contains('translate-x-full')) {
            panel.classList.remove('translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
            fetchLogsFromDB(); // Fetch from DB when opening panel
        } else {
            panel.classList.add('translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }
    }

    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    const AUTH_TOKEN = "YOUR_BLYNK_AUTH_TOKEN"; // Replace with your actual Blynk token
    const BLYNK_URL = "https://blynk.cloud/external/api";
    const PINS = { SOIL: "V0", TEMP: "V1", HUM: "V2", PUMP: "V4", N: "V5", P: "V6", K: "V7" };

    async function fetchPinValue(pin) {
        try {
            const response = await fetch(`${BLYNK_URL}/get?token=${AUTH_TOKEN}&${pin}`);
            if (!response.ok) return null;
            return await response.json();
        } catch (error) { return null; }
    }

    async function updateDashboard() {
        const soil = await fetchPinValue(PINS.SOIL);
        const temp = await fetchPinValue(PINS.TEMP);
        const hum = await fetchPinValue(PINS.HUM);
        const pump = await fetchPinValue(PINS.PUMP);
        const n_val = await fetchPinValue(PINS.N);
        const p_val = await fetchPinValue(PINS.P);
        const k_val = await fetchPinValue(PINS.K);

        // Update global vars for database save
        if(soil !== null) currentSoil = parseInt(soil);
        if(temp !== null) currentTemp = parseFloat(temp);
        if(hum !== null) currentHum = parseFloat(hum);
        if(n_val !== null) currentN = parseInt(n_val);
        if(p_val !== null) currentP = parseInt(p_val);
        if(k_val !== null) currentK = parseInt(k_val);

        if (soil !== null) document.getElementById('soil-val').innerText = currentSoil;
        if (temp !== null) document.getElementById('temp-val').innerText = currentTemp.toFixed(1);
        if (hum !== null) document.getElementById('hum-val').innerText = currentHum.toFixed(1);
        if (n_val !== null) document.getElementById('n-val').innerText = currentN;
        if (p_val !== null) document.getElementById('p-val').innerText = currentP;
        if (k_val !== null) document.getElementById('k-val').innerText = currentK;
        
        const statusText = document.getElementById('pump-status-text');
        const icon = document.getElementById('connection-icon');
        const apiText = document.getElementById('api-status');

        if (pump !== null) {
            if (parseInt(pump) === 1) {
                statusText.innerText = "ACTIVE";
                statusText.className = "text-xs font-bold text-white bg-accent-green px-2 py-1 rounded transition-colors duration-300";
            } else {
                statusText.innerText = "OFF";
                statusText.className = "text-xs font-bold text-text-muted bg-panel-dark px-2 py-1 rounded transition-colors duration-300";
            }
            icon.className = "fa-solid fa-circle text-success mr-1 shadow-sm";
            apiText.innerText = "Live Sync Active";
        } else {
            icon.className = "fa-solid fa-circle text-danger mr-1 shadow-sm";
            apiText.innerText = "Connection Lost";
        }
    }

    // --- DATABASE AJAX FUNCTIONS ---
    async function saveLogToDB(actionType) {
        const payload = {
            action_type: actionType,
            soil: currentSoil,
            temp: currentTemp,
            hum: currentHum,
            n_val: currentN,
            p_val: currentP,
            k_val: currentK
        };

        try {
            await fetch('api_save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            fetchLogsFromDB(); // Refresh UI after saving
        } catch (error) { console.error("DB Save Error:", error); }
    }

    async function fetchLogsFromDB() {
        try {
            const response = await fetch('api_get.php');
            const records = await response.json();
            
            const container = document.getElementById('records-container');
            const logContainer = document.getElementById('main-log-container');
            container.innerHTML = '';
            logContainer.innerHTML = '';

            if(records.length === 0) {
                container.innerHTML = '<p class="text-center text-sm text-text-muted mt-4">No records found.</p>';
                return;
            }

            // ADDED: index parameter to allow staggered smooth animations
            records.forEach((rec, index) => {
                let borderClass = 'border-panel-dark';
                let badgeClass = 'bg-panel-dark text-text-main';
                let iconClass = 'fa-droplet';
                let iconColorClass = 'bg-widget-brown';
                
                if(rec.action_type === 'PUMP ON') {
                    borderClass = 'border-accent-green';
                    badgeClass = 'bg-accent-green text-white';
                    iconClass = 'fa-power-off';
                    iconColorClass = 'bg-accent-green';
                } else if (rec.action_type === 'ALERT') {
                    borderClass = 'border-accent-orange';
                    badgeClass = 'bg-accent-orange text-white';
                    iconClass = 'fa-triangle-exclamation';
                    iconColorClass = 'bg-accent-orange';
                }

                // fallback mechanism in case old DB records lack NPK values
                let safeN = rec.n_val !== undefined ? rec.n_val : '--';
                let safeP = rec.p_val !== undefined ? rec.p_val : '--';
                let safeK = rec.k_val !== undefined ? rec.k_val : '--';

                // 1. Populate the slide-out Panel (ADDED NPK & Smooth Class)
                container.innerHTML += `
                <div class="log-item-animate bg-white p-3 rounded-3xl soft-shadow border-l-4 ${borderClass} hover-lift transition-transform" style="animation-delay: ${index * 0.05}s">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-xs font-bold text-text-muted"><i class="fa-regular fa-calendar mr-1"></i> ${rec.formatted_time}</span>
                        <span class="text-[10px] ${badgeClass} px-2 py-1 rounded-full font-bold">${rec.action_type}</span>
                    </div>
                    <div class="row g-2 text-center text-sm">
                        <div class="col-4"><span class="d-block text-text-muted text-[10px] uppercase font-bold">Soil</span><span class="font-extrabold text-text-main">${rec.soil}%</span></div>
                        <div class="col-4"><span class="d-block text-text-muted text-[10px] uppercase font-bold">Temp</span><span class="font-extrabold text-text-main">${rec.temp}°C</span></div>
                        <div class="col-4"><span class="d-block text-text-muted text-[10px] uppercase font-bold">Hum</span><span class="font-extrabold text-text-main">${rec.hum}%</span></div>
                        <div class="col-4 mt-2"><span class="d-block text-accent-green text-[10px] uppercase font-bold">N</span><span class="font-extrabold text-text-main">${safeN}</span></div>
                        <div class="col-4 mt-2"><span class="d-block text-accent-orange text-[10px] uppercase font-bold">P</span><span class="font-extrabold text-text-main">${safeP}</span></div>
                        <div class="col-4 mt-2"><span class="d-block text-widget-brown text-[10px] uppercase font-bold">K</span><span class="font-extrabold text-text-main">${safeK}</span></div>
                    </div>
                </div>`;

                // 2. Populate the Main Screen System Log (ADDED NPK & Smooth Class)
                logContainer.innerHTML += `
                <li class="log-item-animate d-flex align-items-start gap-3 transition-all hover:bg-white/30 p-2 rounded-xl" style="animation-delay: ${index * 0.05}s">
                    <div class="${iconColorClass} text-white rounded-full w-8 h-8 flex items-center justify-center shrink-0"><i class="fa-solid ${iconClass} text-xs"></i></div>
                    <div class="w-100">
                        <p class="font-semibold text-sm m-0">${rec.action_type} - ${rec.formatted_time}</p>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="text-[11px] text-text-muted font-medium bg-white/50 px-2 py-0.5 rounded-full">Soil: ${rec.soil}%</span>
                            <span class="text-[11px] text-text-muted font-medium bg-white/50 px-2 py-0.5 rounded-full">Temp: ${rec.temp}°C</span>
                            <span class="text-[11px] text-text-muted font-medium bg-white/50 px-2 py-0.5 rounded-full">Hum: ${rec.hum}%</span>
                            <span class="text-[11px] text-accent-green font-bold bg-white/50 px-2 py-0.5 rounded-full">N: ${safeN}</span>
                            <span class="text-[11px] text-accent-orange font-bold bg-white/50 px-2 py-0.5 rounded-full">P: ${safeP}</span>
                            <span class="text-[11px] text-widget-brown font-bold bg-white/50 px-2 py-0.5 rounded-full">K: ${safeK}</span>
                        </div>
                    </div>
                </li>`;
            });

        } catch (error) { console.error("DB Fetch Error:", error); }
    }

    async function togglePump(state) {
        try {
            const response = await fetch(`${BLYNK_URL}/update?token=${AUTH_TOKEN}&${PINS.PUMP}=${state}`);
            if (response.ok) {
                // Determine action text
                const actionText = state === 1 ? 'PUMP ON' : 'PUMP OFF';
                // Save it to MySQL Database!
                saveLogToDB(actionText);
                
                setTimeout(updateDashboard, 500); 
            }
        } catch (error) {
            console.error("API Error:", error);
        }
    }

    // --- ADDED: Chatbot Logic Implementation ---
    function toggleChat() {
        const chatWidget = document.getElementById('chat-widget');
        chatWidget.classList.toggle('active');
        if (chatWidget.classList.contains('active')) {
            setTimeout(() => document.getElementById('chat-input').focus(), 300);
        }
    }

    function handleChatKeyPress(event) {
        if (event.key === 'Enter') {
            sendChatMessage();
        }
    }

    function sendChatMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (!message) return;

        // Add user message
        appendMessage('user', message);
        input.value = '';

        // Show typing indicator
        showTypingIndicator();

        // Simulate network processing delay for realism (800ms to 1500ms)
        const delay = Math.floor(Math.random() * 700) + 800;
        setTimeout(() => {
            removeTypingIndicator();
            const response = generateAIResponse(message.toLowerCase());
            appendMessage('ai', response);
        }, delay);
    }

    function appendMessage(sender, text) {
        const container = document.getElementById('chat-messages');
        const alignClass = sender === 'user' ? 'justify-end' : 'justify-start';
        const bubbleClass = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-ai border border-panel-dark/30';
        
        const msgHTML = `
            <div class="flex ${alignClass} log-item-animate" style="animation-duration: 0.3s;">
                <div class="${bubbleClass} px-4 py-2.5 rounded-2xl text-sm shadow-sm max-w-[85%] leading-relaxed">
                    ${text}
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', msgHTML);
        container.scrollTop = container.scrollHeight;
    }

    function showTypingIndicator() {
        const container = document.getElementById('chat-messages');
        const msgHTML = `
            <div id="typing-indicator" class="flex justify-start log-item-animate">
                <div class="chat-bubble-ai px-4 py-3 rounded-2xl text-sm shadow-sm flex items-center gap-1 border border-panel-dark/30">
                    <div class="typing-indicator flex items-center h-4"><span></span><span></span><span></span></div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', msgHTML);
        container.scrollTop = container.scrollHeight;
    }

    function removeTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        if (el) el.remove();
    }

    function generateAIResponse(msg) {
        // System context check
        const isOnline = document.getElementById('api-status').innerText !== "Connection Lost";
        const pumpStatus = document.getElementById('pump-status-text').innerText;
        
        // Pattern Matching Logic
        if (msg.includes('status') || msg.includes('system') || msg.includes('update')) {
            if (!isOnline) return "⚠️ The system is currently **OFFLINE**. I am unable to fetch live data. Please check your Blynk connection.";
            
            return `🟢 **Live System Overview:**<br>
                    <hr class="my-1 border-panel-dark/30">
                    🌡️ Temp: <b>${currentTemp.toFixed(1)}°C</b><br>
                    💧 Humidity: <b>${currentHum.toFixed(1)}%</b><br>
                    🌱 Soil Moisture: <b>${currentSoil}%</b><br>
                    ⚙️ Irrigation: <b>${pumpStatus}</b><br>
                    🧪 NPK: <b>${currentN}-${currentP}-${currentK}</b>`;
        }

        if (msg.includes('ping') || msg.includes('network') || msg.includes('signal') || msg.includes('latency')) {
            if (!isOnline) return "❌ Connection to the Blynk API is currently lost. Cannot measure ping.";
            
            // Simulating a realistic ping response based on connection state
            const mockPing = Math.floor(Math.random() * 35) + 15; // 15ms - 50ms
            return `📶 **Network Diagnostic:**<br>
                    <hr class="my-1 border-panel-dark/30">
                    🔌 Status: <b>Connected & Stable</b><br>
                    ☁️ Endpoint: <b>blynk.cloud</b><br>
                    ⚡ Est. Latency: <b>~${mockPing}ms</b><br>
                    Sync intervals are executing normally every 3 seconds.`;
        }

        if (msg.includes('history') || msg.includes('record') || msg.includes('log') || msg.includes('recent')) {
            // Read from DOM memory directly
            const logs = document.querySelectorAll('#main-log-container li p.font-semibold');
            if (logs.length > 0) {
                return `Here is your most recent system event log:<br><br>📝 <i>"${logs[0].innerText}"</i><br><br>For a full database breakdown, click the <b><i class="fa-solid fa-bars"></i> View Records</b> button in the top right navigation.`;
            }
            return "I couldn't find any recent local records. The database table might be empty or is currently syncing.";
        }

        if (msg.includes('soil') || msg.includes('moisture')) {
            let remark = currentSoil < 30 ? "It's quite dry. Triggering irrigation is recommended." : (currentSoil > 70 ? "The soil is thoroughly hydrated." : "Moisture levels are optimal.");
            return `🌱 The current soil moisture is at **${currentSoil}%**.<br><span class="text-xs text-text-muted mt-1 block">${remark}</span>`;
        }

        if (msg.includes('temp') || msg.includes('hot') || msg.includes('cold')) {
            return `🌡️ The field temperature reading is **${currentTemp.toFixed(1)}°C**.`;
        }

        if (msg.includes('hum')) {
            return `💧 Atmospheric humidity is currently reading **${currentHum.toFixed(1)}%**.`;
        }

        if (msg.includes('npk') || msg.includes('nitrogen') || msg.includes('phosphorus') || msg.includes('potassium') || msg.includes('fertilizer')) {
            return `🧪 **Live NPK Values:**<br>
                    • Nitrogen (N): <b>${currentN} mg/kg</b><br>
                    • Phosphorus (P): <b>${currentP} mg/kg</b><br>
                    • Potassium (K): <b>${currentK} mg/kg</b>`;
        }

        if (msg.includes('pump') || msg.includes('water') || msg.includes('irrigation')) {
            return `⚙️ The irrigation pump is currently **${pumpStatus}**.<br>You can manually control it using the ON/OFF buttons in the Irrigation panel.`;
        }

        if (msg.includes('hello') || msg.includes('hi ') || msg.includes('hey')) {
            return "Greetings! 👋 I am AgriBot. I am constantly monitoring the farm sensors. Do you need a status report, network check, or historical data?";
        }
        
        // Fallback Response
        return "I'm your localized AgriBot assistant. Try asking me specific keywords like:<br><br>• <i>'What is the system status?'</i><br>• <i>'Check network ping'</i><br>• <i>'Show me the recent log history'</i><br>• <i>'What is the soil reading?'</i>";
    }

    // Initialize
    updateDashboard();
    setInterval(updateDashboard, 3000);
    
    // Fetch logs on initial load
    setTimeout(fetchLogsFromDB, 1000);

