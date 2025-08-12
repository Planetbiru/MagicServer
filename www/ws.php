<?php
header("Content-Type: text/javascript; charset=UTF-8");
?>
let ws;
let reconnectInterval = 3000; // 3 detik

function log(msg) {
    console.log(new Date().toLocaleTimeString() + " - " + msg);
}

function connect() {
    ws = new WebSocket("ws://localhost:8081");

    ws.onopen = () => {
        log("✅ Connected to WebSocket server");
        ws.send("Hello Server!");
    };

    ws.onmessage = (event) => {
        log("📩 Message from server: " + event.data);
    };

    ws.onerror = (err) => {
        log("❌ WebSocket error: " + err);
    };

    ws.onclose = () => {
        log("⚠️ WebSocket disconnected. Reconnecting in " + (reconnectInterval/1000) + "s...");
        setTimeout(connect, reconnectInterval);
    };
}

connect();
