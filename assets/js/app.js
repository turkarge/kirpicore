(function () {
    "use strict";

    const KirpiCore = {
        baseUrl: window.location.origin,
        csrfToken: null,
        modalInstance: null,
        secondaryModalInstance: null,
        confirmCallback: null,
        lastTriggerElement: null,

        init() {
            this.bootstrapGlobals();
            this.initToastr();
            this.showFlashMessage();
            this.showPendingToast();
            this.initMainModal();
            this.initSecondaryModal();
            this.initDropdowns();
            this.bindModalTriggers();
            this.bindConfirmTriggers();
            this.bindAjaxForms();
        },

        bootstrapGlobals() {
            if (window.KIRPI_CONFIG) {
                this.baseUrl = window.KIRPI_CONFIG.baseUrl || this.baseUrl;
                this.csrfToken = window.KIRPI_CONFIG.csrfToken || null;
                this.flashMessage = window.KIRPI_CONFIG.flashMessage || null;
            }
        },

        initToastr() {
            if (!window.toastr) {
                return;
            }

            toastr.options = {
                closeButton: true,
                progressBar: true,
                newestOnTop: true,
                positionClass: "toast-top-right",
                timeOut: 3500,
                extendedTimeOut: 1500
            };
        },

        showFlashMessage() {
            if (!this.flashMessage || !this.flashMessage.message) {
                return;
            }

            const flashTypeMap = {
                success: "success",
                danger: "error",
                error: "error",
                warning: "warning",
                info: "info"
            };

            this.toast(
                this.flashMessage.message,
                flashTypeMap[this.flashMessage.type] || "info"
            );

            this.flashMessage = null;
        },

        showPendingToast() {
            try {
                const raw = window.sessionStorage.getItem("kirpi_pending_toast");
                if (!raw) {
                    return;
                }

                window.sessionStorage.removeItem("kirpi_pending_toast");

                const toast = JSON.parse(raw);
                if (!toast || !toast.message) {
                    return;
                }

                this.toast(toast.message, toast.type || "info");
            } catch (error) {
                console.warn("Pending toast okunamadı:", error);
            }
        },

        persistPendingToast(message, type = "info") {
            if (!message) {
                return;
            }

            try {
                window.sessionStorage.setItem("kirpi_pending_toast", JSON.stringify({
                    message: message,
                    type: type
                }));
            } catch (error) {
                console.warn("Pending toast kaydedilemedi:", error);
            }
        },

        initMainModal() {
            const modalEl = document.getElementById("main-modal");
            if (!modalEl) return;

            if (window.bootstrap && bootstrap.Modal) {
                this.modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

                modalEl.addEventListener("hidden.bs.modal", () => {
                    if (this.lastTriggerElement && typeof this.lastTriggerElement.focus === "function") {
                        this.lastTriggerElement.focus();
                    }
                });
            } else {
                console.warn("Bootstrap modal API bulunamadı: main-modal");
            }
        },

        initSecondaryModal() {
            const modalEl = document.getElementById("secondary-modal");
            if (!modalEl) return;

            if (window.bootstrap && bootstrap.Modal) {
                this.secondaryModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

                modalEl.addEventListener("hidden.bs.modal", () => {
                    if (this.lastTriggerElement && typeof this.lastTriggerElement.focus === "function") {
                        this.lastTriggerElement.focus();
                    }
                });
            } else {
                console.warn("Bootstrap modal API bulunamadı: secondary-modal");
            }
        },

        initDropdowns() {
            if (!(window.bootstrap && bootstrap.Dropdown)) {
                console.warn("Bootstrap Dropdown API bulunamadı.");
                return;
            }

            const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');

            dropdowns.forEach((el) => {
                const instance = bootstrap.Dropdown.getOrCreateInstance(el);

                el.addEventListener("click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    instance.toggle();
                });
            });

            document.addEventListener("click", function (event) {
                dropdowns.forEach((el) => {
                    const parent = el.closest(".dropdown");
                    if (!parent) return;

                    if (!parent.contains(event.target)) {
                        const instance = bootstrap.Dropdown.getOrCreateInstance(el);
                        instance.hide();
                    }
                });
            });
        },

        async get(url, options = {}) {
            return await fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    ...options.headers
                }
            });
        },

        async post(url, data = {}, options = {}) {
            const formData = new FormData();

            Object.keys(data).forEach((key) => {
                formData.append(key, data[key]);
            });

            if (this.csrfToken && !formData.has("csrf_token")) {
                formData.append("csrf_token", this.csrfToken);
            }

            return await fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    ...options.headers
                }
            });
        },

        showLoading(target) {
            if (!target) return;

            target.innerHTML = `
                <div class="kirpi-loading">
                    <div class="spinner-border" role="status"></div>
                </div>
            `;
        },

        closeModal(target = "main") {
            const modalId = target === "secondary" ? "secondary-modal" : "main-modal";
            const modalEl = document.getElementById(modalId);

            if (!modalEl) {
                return;
            }

            this.forceCloseModalElement(modalEl);
        },

        closeModalElement(modalEl) {
            if (!modalEl) {
                return;
            }

            try {
                if (window.bootstrap && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    instance.hide();
                }
            } catch (error) {
                console.warn("Bootstrap modal hide hatası:", error);
            }

            this.forceCloseModalElement(modalEl);
        },

        forceCloseModalElement(modalEl) {
            if (!modalEl) {
                return;
            }

            try {
                if (window.bootstrap && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) {
                        instance.hide();
                    }
                }
            } catch (error) {
                console.warn("Bootstrap modal hide hatası:", error);
            }

            // fallback: bootstrap hide nazlanırsa manuel temizle
            setTimeout(() => {
                modalEl.classList.remove("show");
                modalEl.style.display = "none";
                modalEl.removeAttribute("aria-modal");
                modalEl.setAttribute("aria-hidden", "true");

                document.body.classList.remove("modal-open");
                document.body.style.removeProperty("padding-right");
                document.body.style.removeProperty("overflow");

                document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
                    backdrop.remove();
                });
            }, 150);
        },

        async openModal(url, title = "", size = "modal-lg", target = "main") {
            const modalId = target === "secondary" ? "secondary-modal" : "main-modal";
            const contentId = target === "secondary" ? "secondary-modal-content" : "main-modal-content";

            const modalEl = document.getElementById(modalId);
            const contentEl = document.getElementById(contentId);

            if (!modalEl || !contentEl) return;

            const dialog = modalEl.querySelector(".modal-dialog");
            if (dialog) {
                dialog.className = "modal-dialog modal-dialog-centered " + size;
            }

            this.showLoading(contentEl);

            const instance = target === "secondary"
                ? this.secondaryModalInstance
                : this.modalInstance;

            if (instance && typeof instance.show === "function") {
                instance.show();
            } else {
                console.warn("Modal instance oluşturulamadı:", target);
            }

            try {
                const response = await this.get(url);
                const html = await response.text();
                contentEl.innerHTML = html;
            } catch (error) {
                contentEl.innerHTML = `
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            İçerik yüklenirken bir hata oluştu.
                        </div>
                    </div>
                `;
            }
        },

        bindModalTriggers() {
            document.addEventListener("click", (event) => {
                const trigger = event.target.closest(".btn-modal-trigger");
                if (!trigger) return;

                event.preventDefault();

                const url = trigger.dataset.url;
                const size = trigger.dataset.size || "modal-lg";
                const target = trigger.dataset.target || "main";

                if (!url) return;

                this.lastTriggerElement = trigger;
                this.openModal(this.normalizeUrl(url), "", size, target);
            });
        },

        bindConfirmTriggers() {
            const confirmModalEl = document.getElementById("confirm-modal");
            const confirmYesBtn = document.getElementById("confirm-modal-yes");
            const confirmText = document.getElementById("confirm-modal-text");

            if (!confirmModalEl || !confirmYesBtn || !window.bootstrap) {
                return;
            }

            const confirmModal = new bootstrap.Modal(confirmModalEl);

            document.addEventListener("click", (event) => {
                const trigger = event.target.closest("[data-confirm]");
                if (!trigger) return;

                event.preventDefault();

                const message = trigger.dataset.confirm || "Emin misiniz?";
                confirmText.textContent = message;

                this.confirmCallback = () => {
                    const href = trigger.getAttribute("href");
                    const formId = trigger.dataset.form;

                    if (formId) {
                        const form = document.getElementById(formId);
                        if (form) {
                            form.submit();
                            return;
                        }
                    }

                    if (href && href !== "#") {
                        window.location.href = href;
                    }
                };

                confirmModal.show();
            });

            confirmYesBtn.addEventListener("click", () => {
                if (typeof this.confirmCallback === "function") {
                    this.confirmCallback();
                }
                confirmModal.hide();
            });
        },

        bindAjaxForms() {
            document.addEventListener("submit", async (event) => {
                const rawTarget = event.target;
                const form = rawTarget instanceof HTMLFormElement
                    ? rawTarget
                    : rawTarget.closest("form[data-ajax='true']");

                if (!form || !form.matches("form[data-ajax='true']")) {
                    return;
                }

                event.preventDefault();

                const submitButton = form.querySelector("[type='submit']");
                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const formData = new FormData(form);

                    if (this.csrfToken && !formData.has("csrf_token")) {
                        formData.append("csrf_token", this.csrfToken);
                    }

                    const response = await fetch(form.action, {
                        method: form.method || "POST",
                        body: formData,
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });

                    const result = await response.json();

                    if (result.message) {
                        this.toast(result.message, result.status || "info");
                    }

                    if (result.status === "success" && form.dataset.closeModal === "true") {
                        this.closeModalElement(form.closest(".modal"));
                    }

                    document.dispatchEvent(new CustomEvent("kirpi:form.success", {
                        detail: {
                            form: form,
                            result: result
                        }
                    }));

                    if (result.reload_page) {
                        if (result.message) {
                            this.persistPendingToast(result.message, result.status || "info");
                        }
                        window.location.reload();
                        return;
                    }

                    if (result.redirect) {
                        window.location.href = result.redirect;
                        return;
                    }
                } catch (error) {
                    console.error("AJAX form submit error:", error);
                    this.toast("İşlem sırasında bir hata oluştu.", "error");
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        },

        toast(message, type = "info") {
            if (window.toastr && typeof toastr[type] === "function") {
                try {
                    toastr[type](message);
                    return;
                } catch (error) {
                    console.warn("Toastr hatası:", error);
                }
            }

            alert(message);
        },

        normalizeUrl(url) {
            if (!url) return url;
            if (url.startsWith("http://") || url.startsWith("https://")) return url;
            if (url.startsWith("/")) return this.baseUrl + url;
            return this.baseUrl + "/" + url;
        }
    };

    window.KirpiCore = KirpiCore;

    document.addEventListener("DOMContentLoaded", function () {
        KirpiCore.init();
    });
})();
