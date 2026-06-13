(function (window, document) {
    "use strict";

    const escape = (value) => String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");

    const language = {
        emptyTable: "Tabloda veri bulunmuyor",
        info: "_TOTAL_ kayıttan _START_ - _END_ arası",
        infoEmpty: "Kayıt yok",
        infoFiltered: "(_MAX_ kayıt içinden filtrelendi)",
        lengthMenu: "Sayfada _MENU_ kayıt",
        loadingRecords: "Yükleniyor...",
        processing: "Veriler hazırlanıyor...",
        search: "Ara:",
        searchPlaceholder: "Tabloda ara...",
        zeroRecords: "Eşleşen kayıt bulunamadı",
        paginate: { first: "İlk", last: "Son", next: "Sonraki", previous: "Önceki" },
        select: { rows: { _: "%d kayıt seçildi", 0: "", 1: "1 kayıt seçildi" } }
    };

    const buildServerExportUrl = (config, dt, format) => {
        const params = new URLSearchParams({ format });
        const search = dt.search();
        if (search) params.set("search", search);
        const filters = config.filters ? config.filters(dt) : {};
        Object.entries(filters || {}).forEach(([key, value]) => {
            if (value !== "" && value !== null && value !== undefined) params.set(key, value);
        });
        return `${config.endpoint}?${params.toString()}`;
    };

    const exportButtons = (options) => {
        const columns = options.exportColumns || ":visible:not(:first-child):not(:last-child)";
        const common = {
            exportOptions: { columns, modifier: { selected: null } },
            title: options.exportTitle || document.title
        };
        const items = [
            { extend: "copyHtml5", text: '<i class="ti ti-copy me-2"></i>Kopyala', ...common },
            { extend: "csvHtml5", text: '<i class="ti ti-file-type-csv me-2"></i>CSV (görünen)', bom: true, ...common },
            { extend: "excelHtml5", text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel (görünen)', ...common },
            { extend: "print", text: '<i class="ti ti-printer me-2"></i>Yazdır', ...common }
        ];

        if (options.serverExport?.endpoint) {
            items.push(
                {
                    text: '<i class="ti ti-database-export me-2"></i>CSV (tüm sonuçlar)',
                    action: (_, dt) => { window.location.href = buildServerExportUrl(options.serverExport, dt, "csv"); }
                },
                {
                    text: '<i class="ti ti-database-export me-2"></i>Excel (tüm sonuçlar)',
                    action: (_, dt) => { window.location.href = buildServerExportUrl(options.serverExport, dt, "xls"); }
                }
            );
        }

        return items;
    };

    const create = (element, options) => {
        if (!window.DataTable) {
            throw new Error("DataTables yüklenmedi.");
        }

        const stateKey = options.stateKey || element.id || "table";
        const filterRow = document.createElement("tr");
        filterRow.className = "kirpi-table-column-filters";
        (options.columnFilters || options.columns.map(() => null)).forEach((filter, index) => {
            const cell = document.createElement("th");
            if (filter) {
                let control;
                if (filter.type === "select") {
                    control = document.createElement("select");
                    control.className = "form-select form-select-sm";
                    (filter.options || []).forEach((option) => {
                        const item = document.createElement("option");
                        item.value = option.value;
                        item.textContent = option.label;
                        control.appendChild(item);
                    });
                } else {
                    control = document.createElement("input");
                    control.type = "search";
                    control.className = "form-control form-control-sm";
                    control.placeholder = filter.placeholder || "Filtrele";
                }
                control.dataset.columnFilter = String(index);
                control.dataset.columnName = options.columns[index]?.name || options.columns[index]?.data || String(index);
                control.dataset.tableFilter = element.id;
                control.setAttribute("aria-label", filter.label || filter.placeholder || "Kolon filtresi");
                cell.appendChild(control);
            }
            filterRow.appendChild(cell);
        });
        element.tHead?.appendChild(filterRow);

        const table = new DataTable(element, {
            processing: true,
            serverSide: true,
            deferRender: true,
            searchDelay: 350,
            stateSave: true,
            stateDuration: 60 * 60 * 24 * 30,
            stateSaveCallback: (_, data) => localStorage.setItem(`kirpi_table_${stateKey}`, JSON.stringify(data)),
            stateLoadCallback: () => {
                try { return JSON.parse(localStorage.getItem(`kirpi_table_${stateKey}`) || "null"); }
                catch (_) { return null; }
            },
            ajax: options.ajax,
            columns: options.columns,
            order: options.order || [],
            orderMulti: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            autoWidth: false,
            titleRow: 0,
            responsive: { details: { type: "inline", target: "tr" } },
            select: { style: "multi+shift", selector: "td:first-child", headerCheckbox: "select-page" },
            colReorder: { columns: ":not(:first-child):not(:last-child)" },
            fixedHeader: { header: true, headerOffset: document.querySelector(".navbar")?.offsetHeight || 0 },
            keys: { columns: ":not(:first-child):not(:last-child)", keys: [9, 13, 37, 38, 39, 40] },
            rowId: options.rowId,
            language,
            layout: {
                topStart: {
                    buttons: [
                        { extend: "collection", text: '<i class="ti ti-download me-2"></i>Dışa aktar', buttons: exportButtons(options) },
                        {
                            extend: "collection",
                            text: '<i class="ti ti-columns-3 me-2"></i>Kolonlar',
                            buttons: [
                                { extend: "columnsToggle", columns: ":not(:first-child):not(:last-child)" },
                                {
                                    text: '<i class="ti ti-restore me-2"></i>Görünümü sıfırla',
                                    action: (_, dt) => {
                                        localStorage.removeItem(`kirpi_table_${stateKey}`);
                                        dt.state.clear();
                                        window.location.reload();
                                    }
                                }
                            ]
                        },
                        { text: '<i class="ti ti-refresh"></i><span class="visually-hidden">Yenile</span>', titleAttr: "Tabloyu yenile", className: "btn-icon kirpi-table-refresh", action: (_, dt) => dt.ajax.reload(null, false) }
                    ]
                },
                topEnd: "search",
                bottomStart: ["pageLength", "info"],
                bottomEnd: "paging"
            }
        });

        let filterTimer = null;
        const filterColumn = (control) => {
            const name = control.dataset.columnName;
            return name && !/^\d+$/.test(name) ? table.column(`${name}:name`) : table.column(Number(control.dataset.columnFilter));
        };
        const syncColumnFilters = () => {
            options.columns.forEach((column, index) => {
                const name = column.name || column.data || String(index);
                const apiColumn = name && !/^\d+$/.test(String(name)) ? table.column(`${name}:name`) : table.column(index);
                const value = apiColumn.search();
                document.querySelectorAll(`[data-table-filter="${element.id}"][data-column-filter="${index}"]`).forEach((control) => {
                    if (control.value !== value) control.value = value;
                });
            });
        };
        const handleColumnFilter = (event) => {
            const control = event.target.closest(`[data-table-filter="${element.id}"][data-column-filter]`);
            if (!control) return;
            if (event.type === "click") {
                event.stopPropagation();
                return;
            }
            if (event.type === "input" && control.tagName === "SELECT") return;
            if (event.type === "change" && control.tagName !== "SELECT") return;
            const column = filterColumn(control);
            clearTimeout(filterTimer);
            filterTimer = setTimeout(() => {
                const value = control.value.trim();
                if (column.search() !== value) {
                    column.search(value).draw();
                }
            }, control.tagName === "SELECT" ? 0 : 300);
        };
        document.addEventListener("input", handleColumnFilter);
        document.addEventListener("change", handleColumnFilter);
        document.addEventListener("click", handleColumnFilter);
        table.on("draw column-reorder column-visibility", syncColumnFilters);
        syncColumnFilters();

        element.addEventListener("click", (event) => {
            const toggle = event.target.closest(".js-kirpi-row-menu");
            if (!toggle || !(window.bootstrap && bootstrap.Dropdown)) return;
            event.preventDefault();
            event.stopPropagation();
            bootstrap.Dropdown.getOrCreateInstance(toggle, { boundary: "viewport", popperConfig: { strategy: "fixed" } }).toggle();
        });

        document.addEventListener("kirpi:theme.changed", () => {
            table.columns.adjust().responsive.recalc();
        });

        return table;
    };

    const post = async (path, data) => {
        const formData = new FormData();
        Object.entries(data || {}).forEach(([key, value]) => formData.append(key, value));
        formData.append("csrf_token", window.KIRPI_CONFIG?.csrfToken || "");
        const base = (window.KIRPI_CONFIG?.baseUrl || "").replace(/\/$/, "");
        const response = await fetch(`${base}/${String(path).replace(/^\//, "")}`, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            body: formData
        });
        const result = await response.json().catch(() => ({ status: "error", message: "Sunucu yanıtı okunamadı." }));
        if (!response.ok || result.status === "error") {
            throw new Error(result.message || "İşlem tamamlanamadı.");
        }
        return result;
    };

    const notify = (result) => {
        if (window.toastr) toastr.success(result.message || "İşlem tamamlandı.");
    };

    const notifyError = (error) => {
        if (window.toastr) toastr.error(error?.message || "İşlem tamamlanamadı.");
    };

    window.KirpiTable = { create, escape, post, notify, notifyError };
})(window, document);
