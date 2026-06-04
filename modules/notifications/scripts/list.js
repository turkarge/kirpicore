document.addEventListener("DOMContentLoaded", function () {
    const tableContainer = document.getElementById("notifications-table-container");
    const searchInput = document.getElementById("notifications-search");
    const statusFilter = document.getElementById("notifications-status-filter");
    const sourceFilter = document.getElementById("notifications-source-filter");
    const templateFilter = document.getElementById("notifications-template-filter");
    const exportButtons = document.querySelectorAll(".js-notifications-export");

    if (!tableContainer) {
        return;
    }

    let currentPage = 1;
    let debounceTimer = null;

    function buildUrl(page = 1) {
        const params = new URLSearchParams();
        params.set("page", page);
        appendFilters(params);

        return `${window.KIRPI_CONFIG.baseUrl}/ajax/notifications/table?${params.toString()}`;
    }

    function appendFilters(params) {
        if (searchInput && searchInput.value.trim() !== "") {
            params.set("search", searchInput.value.trim());
        }

        if (statusFilter && statusFilter.value !== "") {
            params.set("status", statusFilter.value);
        }

        if (sourceFilter && sourceFilter.value !== "") {
            params.set("source_module", sourceFilter.value);
        }

        if (templateFilter && templateFilter.value !== "") {
            params.set("template_key", templateFilter.value);
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
            const i18n = window.KIRPI_NOTIFICATIONS_I18N || {};
            const loadErrorText = i18n.listLoadError || "Bildirim listesi yüklenirken bir hata oluştu.";

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

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(triggerReload, 300);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", triggerReload);
    }

    if (sourceFilter) {
        sourceFilter.addEventListener("change", triggerReload);
    }

    if (templateFilter) {
        templateFilter.addEventListener("change", triggerReload);
    }

    exportButtons.forEach((button) => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            const params = new URLSearchParams();
            params.set("format", button.dataset.format || "csv");
            appendFilters(params);
            window.location.href = `${window.KIRPI_CONFIG.baseUrl}/notifications/actions/export?${params.toString()}`;
        });
    });

    document.addEventListener("click", function (event) {
        const paginationLink = event.target.closest(".pagination .page-link");
        if (!paginationLink || !paginationLink.closest("#notifications-table-container")) {
            return;
        }

        event.preventDefault();

        const page = parseInt(paginationLink.dataset.page || "1", 10);
        if (!Number.isNaN(page)) {
            loadTable(page);
        }
    });

    document.addEventListener("kirpi:form.success", function (event) {
        const form = event.detail.form;
        const result = event.detail.result || {};

        if (!form) {
            return;
        }

        const isMarkRead = form.classList.contains("notifications-mark-read-form");
        const isMarkAllRead = form.id === "notifications-mark-all-read-form";

        if (!isMarkRead && !isMarkAllRead) {
            return;
        }

        if (result.status === "success") {
            setTimeout(function () {
                loadTable(currentPage);
            }, 150);
        }
    });

    if (searchInput && !searchInput.disabled) {
        loadTable(1);
    }
});
