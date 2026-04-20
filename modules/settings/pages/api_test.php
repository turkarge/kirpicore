<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">API Test Merkezi</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Method</label>
                        <select id="api-test-method" class="form-select">
                            <option value="GET" selected>GET</option>
                            <option value="POST">POST</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-9">
                        <label class="form-label">Endpoint</label>
                        <input id="api-test-endpoint" type="text" class="form-control" value="/api/v1/me" placeholder="/api/v1/users?page=1&per_page=5">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bearer Token</label>
                        <input id="api-test-token" type="text" class="form-control" placeholder="eyJ...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">JSON Body (POST/PATCH/DELETE)</label>
                        <textarea id="api-test-body" class="form-control font-monospace" rows="8" placeholder="{&#10;  &quot;name&quot;: &quot;Test User&quot;&#10;}"></textarea>
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" id="api-test-send-btn">Istek Gonder</button>
                        <button type="button" class="btn btn-outline-secondary" id="api-test-fill-me-btn">/me</button>
                        <button type="button" class="btn btn-outline-secondary" id="api-test-fill-users-btn">/users</button>
                        <button type="button" class="btn btn-outline-secondary" id="api-test-fill-token-btn">/auth/token</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Sonuc</h3>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-blue-lt" id="api-test-status-badge">Hazir</span>
                    <span class="text-secondary ms-2" id="api-test-url-label">-</span>
                </div>
                <pre id="api-test-response" class="mb-0 p-3 rounded bg-dark text-light" style="min-height: 260px; white-space: pre-wrap;">Henüz istek gonderilmedi.</pre>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const methodEl = document.getElementById("api-test-method");
    const endpointEl = document.getElementById("api-test-endpoint");
    const tokenEl = document.getElementById("api-test-token");
    const bodyEl = document.getElementById("api-test-body");
    const sendBtn = document.getElementById("api-test-send-btn");
    const statusBadgeEl = document.getElementById("api-test-status-badge");
    const urlLabelEl = document.getElementById("api-test-url-label");
    const responseEl = document.getElementById("api-test-response");

    const fillMeBtn = document.getElementById("api-test-fill-me-btn");
    const fillUsersBtn = document.getElementById("api-test-fill-users-btn");
    const fillTokenBtn = document.getElementById("api-test-fill-token-btn");

    function setStatus(label, className) {
        statusBadgeEl.className = "badge " + className;
        statusBadgeEl.textContent = label;
    }

    function toAbsoluteUrl(endpoint) {
        const base = String(window.KIRPI_CONFIG?.baseUrl || window.location.origin).replace(/\/+$/, "");
        const path = String(endpoint || "").trim();

        if (path.startsWith("http://") || path.startsWith("https://")) {
            return path;
        }

        if (path.startsWith("/")) {
            return base + path;
        }

        return base + "/" + path;
    }

    fillMeBtn.addEventListener("click", function () {
        methodEl.value = "GET";
        endpointEl.value = "/api/v1/me";
        bodyEl.value = "";
    });

    fillUsersBtn.addEventListener("click", function () {
        methodEl.value = "GET";
        endpointEl.value = "/api/v1/users?page=1&per_page=5";
        bodyEl.value = "";
    });

    fillTokenBtn.addEventListener("click", function () {
        methodEl.value = "POST";
        endpointEl.value = "/api/v1/auth/token";
        bodyEl.value = JSON.stringify({
            email: "admin@kirpi.local",
            password: "123456",
            token_name: "api-test"
        }, null, 2);
    });

    sendBtn.addEventListener("click", async function () {
        const method = String(methodEl.value || "GET").toUpperCase();
        const endpoint = String(endpointEl.value || "").trim();
        const token = String(tokenEl.value || "").trim();
        const bodyRaw = String(bodyEl.value || "").trim();

        if (!endpoint) {
            if (window.KirpiCore) {
                window.KirpiCore.toast("Endpoint bos olamaz.", "warning");
            }
            return;
        }

        const url = toAbsoluteUrl(endpoint);
        urlLabelEl.textContent = url;
        responseEl.textContent = "Istek gonderiliyor...";
        setStatus("Bekleniyor", "bg-yellow-lt");
        sendBtn.disabled = true;

        try {
            const headers = {
                "Accept": "application/json"
            };

            if (token !== "") {
                headers["Authorization"] = "Bearer " + token;
            }

            const options = {
                method: method,
                headers: headers
            };

            if (method !== "GET" && method !== "HEAD") {
                if (bodyRaw !== "") {
                    try {
                        const parsed = JSON.parse(bodyRaw);
                        options.body = JSON.stringify(parsed);
                        headers["Content-Type"] = "application/json";
                    } catch (error) {
                        throw new Error("JSON Body gecersiz: " + error.message);
                    }
                }
            }

            const response = await fetch(url, options);
            const text = await response.text();
            let parsedPayload = null;

            try {
                parsedPayload = JSON.parse(text);
            } catch (error) {
                parsedPayload = text;
            }

            setStatus(String(response.status), response.ok ? "bg-green-lt" : "bg-red-lt");
            responseEl.textContent = typeof parsedPayload === "string"
                ? parsedPayload
                : JSON.stringify(parsedPayload, null, 2);
        } catch (error) {
            setStatus("Hata", "bg-red-lt");
            responseEl.textContent = String(error && error.message ? error.message : error);
        } finally {
            sendBtn.disabled = false;
        }
    });
});
</script>
