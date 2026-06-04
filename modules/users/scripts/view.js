document.addEventListener("DOMContentLoaded", function () {
    const tableContainer = document.getElementById("users-table-container");
    const searchInput = document.getElementById("users-search");
    const roleFilter = document.getElementById("users-role-filter");
    const statusFilter = document.getElementById("users-status-filter");
    const exportButtons = document.querySelectorAll(".js-users-export");

    if (!tableContainer) {
        return;
    }

    let currentPage = 1;
    let debounceTimer = null;

    function appendFilters(params) {
        if (searchInput && searchInput.value.trim() !== "") {
            params.set("search", searchInput.value.trim());
        }

        if (roleFilter && roleFilter.value !== "") {
            params.set("role_id", roleFilter.value);
        }

        if (statusFilter && statusFilter.value !== "") {
            params.set("status", statusFilter.value);
        }
    }

    function buildUrl(page = 1) {
        const params = new URLSearchParams();
        params.set("page", page);
        appendFilters(params);

        return `${window.KIRPI_CONFIG.baseUrl}/ajax/users/table?${params.toString()}`;
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
                        Kullanıcı listesi yüklenirken bir hata oluştu.
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

    if (roleFilter) {
        roleFilter.addEventListener("change", triggerReload);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", triggerReload);
    }

    exportButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const params = new URLSearchParams();
            params.set("format", button.dataset.format || "csv");
            appendFilters(params);
            window.location.href = `${window.KIRPI_CONFIG.baseUrl}/users/actions/export?${params.toString()}`;
        });
    });

    document.addEventListener("click", function (event) {
        const paginationLink = event.target.closest(".pagination .page-link");
        if (!paginationLink) {
            return;
        }

        event.preventDefault();

        const page = parseInt(paginationLink.dataset.page || "1", 10);
        if (!Number.isNaN(page)) {
            loadTable(page);
        }
    });

    document.addEventListener("change", function (event) {
        const switchInput = event.target.closest(".users-status-switch");
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
        const isUsersCreate = formId === "users-create-form";
        const isUsersEdit = formId === "users-edit-form";
        const isUsersToggle = form.classList.contains("users-toggle-status-form");

        if (!isUsersCreate && !isUsersEdit && !isUsersToggle) {
            return;
        }

        if (result.status === "success") {
            if (isUsersCreate || isUsersEdit || result.reload_page) {
                window.location.reload();
                return;
            }

            // Modal kapanırken küçük gecikmeyle tabloyu yenile.
            setTimeout(() => {
                loadTable(currentPage);
            }, 150);
        }
    });

    loadTable(1);
});
