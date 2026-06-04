document.addEventListener("DOMContentLoaded", function () {
    const tableContainer = document.getElementById("roles-table-container");
    const searchInput = document.getElementById("roles-search");
    const statusFilter = document.getElementById("roles-status-filter");
    const exportButtons = document.querySelectorAll(".js-roles-export");

    if (!tableContainer) {
        return;
    }

    let currentPage = 1;
    let debounceTimer = null;

    function appendFilters(params) {
        if (searchInput && searchInput.value.trim() !== "") {
            params.set("search", searchInput.value.trim());
        }

        if (statusFilter && statusFilter.value !== "") {
            params.set("status", statusFilter.value);
        }
    }

    function buildUrl(page = 1) {
        const params = new URLSearchParams();
        params.set("page", page);
        appendFilters(params);

        return `${window.KIRPI_CONFIG.baseUrl}/ajax/roles/table?${params.toString()}`;
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
            tableContainer.innerHTML = `
                <div class="p-4">
                    <div class="alert alert-danger mb-0">
                        Rol listesi yüklenirken bir hata oluştu.
                    </div>
                </div>
            `;
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                loadTable(1);
            }, 300);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", function () {
            loadTable(1);
        });
    }

    exportButtons.forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            const params = new URLSearchParams();
            params.set("type", button.dataset.type || "roles");
            params.set("format", button.dataset.format || "csv");
            appendFilters(params);
            window.location.href = `${window.KIRPI_CONFIG.baseUrl}/roles/actions/export?${params.toString()}`;
        });
    });

    document.addEventListener("click", function (event) {
        const paginationLink = event.target.closest(".pagination .page-link");
        if (!paginationLink) {
            return;
        }

        if (!paginationLink.closest("#roles-table-container")) {
            return;
        }

        event.preventDefault();

        const page = parseInt(paginationLink.dataset.page || "1", 10);
        if (!Number.isNaN(page)) {
            loadTable(page);
        }
    });

    document.addEventListener("change", function (event) {
        const switchInput = event.target.closest(".roles-status-switch");
        if (!switchInput) {
            return;
        }

        const form = switchInput.closest("form");
        if (!form) {
            return;
        }

        const statusInput = form.querySelector('input[name="status"]');
        const newStatus = switchInput.checked ? 1 : 0;

        if (statusInput) {
            statusInput.value = newStatus;
        }

        form.dispatchEvent(new Event("submit", { cancelable: true, bubbles: true }));
    });

    document.addEventListener("kirpi:form.success", function (event) {
        const form = event.detail.form;
        const result = event.detail.result || {};

        if (!form) {
            return;
        }

        const formId = form.id || null;
        const isRolesCreate = formId === "roles-create-form";
        const isRolesEdit = formId === "roles-edit-form";
        const isRolesToggle = form.classList.contains("roles-toggle-status-form");

        if (!isRolesCreate && !isRolesEdit && !isRolesToggle) {
            return;
        }

        if (result.status === "success") {
            setTimeout(function () {
                loadTable(currentPage);
            }, 150);
        }
    });

    loadTable(1);
});
