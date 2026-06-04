document.addEventListener("DOMContentLoaded", function () {
    const tableContainer = document.getElementById("audit-table-container");
    const statusFilter = document.getElementById("audit-status-filter");
    const moduleFilter = document.getElementById("audit-module-filter");
    const actionFilter = document.getElementById("audit-action-filter");
    const userFilter = document.getElementById("audit-user-filter");
    const exportButtons = document.querySelectorAll(".js-audit-export");

    if (!tableContainer) {
        return;
    }

    let debounceTimer = null;
    let currentPage = 1;

    function buildUrl(page = 1) {
        const params = new URLSearchParams();
        params.set("page", page);
        appendFilters(params);

        return `${window.KIRPI_CONFIG.baseUrl}/ajax/audit/table?${params.toString()}`;
    }

    function appendFilters(params) {
        if (statusFilter && statusFilter.value !== "") {
            params.set("status", statusFilter.value);
        }

        if (moduleFilter && moduleFilter.value.trim() !== "") {
            params.set("module", moduleFilter.value.trim());
        }

        if (actionFilter && actionFilter.value.trim() !== "") {
            params.set("action", actionFilter.value.trim());
        }

        if (userFilter && userFilter.value.trim() !== "") {
            params.set("user_id", userFilter.value.trim());
        }
    }

    async function loadTable(page = 1) {
        currentPage = page;

        tableContainer.innerHTML = `
            <div class="kirpi-loading">
                <div class="spinner-border" role="status"></div>
            </div>
        `;

        try {
            const response = await fetch(buildUrl(page), {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const html = await response.text();
            tableContainer.innerHTML = html;
        } catch (error) {
            const i18n = window.KIRPI_AUDIT_I18N || {};
            const loadErrorText = i18n.loadError || "Audit kayitlari yuklenirken bir hata olustu.";

            tableContainer.innerHTML = `
                <div class="p-4">
                    <div class="alert alert-danger mb-0">
                        ${loadErrorText}
                    </div>
                </div>
            `;
        }
    }

    function triggerReload() {
        loadTable(1);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", triggerReload);
    }

    [moduleFilter, actionFilter, userFilter].forEach(function (input) {
        if (!input) {
            return;
        }

        input.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(triggerReload, 300);
        });
    });

    exportButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            const params = new URLSearchParams();
            params.set("format", button.dataset.format || "csv");
            appendFilters(params);
            window.location.href = `${window.KIRPI_CONFIG.baseUrl}/audit/actions/export?${params.toString()}`;
        });
    });

    document.addEventListener("click", function (event) {
        const paginationLink = event.target.closest(".pagination .page-link");
        if (!paginationLink || !paginationLink.closest("#audit-table-container")) {
            return;
        }

        event.preventDefault();

        const page = parseInt(paginationLink.dataset.page || "1", 10);
        if (!Number.isNaN(page)) {
            loadTable(page);
        }
    });

    if (!statusFilter || !statusFilter.disabled) {
        loadTable(1);
    }
});
