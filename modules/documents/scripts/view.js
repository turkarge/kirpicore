(function () {
    "use strict";

    const form = document.querySelector("[data-document-filepond-form]");
    const input = form?.querySelector("[data-document-filepond]");

    if (!form || !input || typeof window.FilePond === "undefined") {
        return;
    }

    const status = form.querySelector("[data-document-upload-status]");
    const submit = form.querySelector("[data-document-upload-submit]");
    const modalElement = document.getElementById("document-upload-modal");
    const maxFileSize = Number(input.dataset.maxFileSize || 0);
    const acceptedFileTypes = (input.dataset.acceptedFileTypes || "")
        .split(",")
        .map((value) => value.trim())
        .filter(Boolean);
    let completedUploads = 0;
    let uploadRunning = false;

    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize
    );

    const setStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const pond = FilePond.create(input, {
        allowMultiple: true,
        allowReorder: true,
        allowRevert: false,
        instantUpload: false,
        maxParallelUploads: 3,
        maxFileSize: maxFileSize || null,
        acceptedFileTypes,
        credits: false,
        labelIdle: '<span class="filepond--label-action">Dosya seçin</span> veya buraya sürükleyin',
        labelInvalidField: "Alan geçersiz dosyalar içeriyor",
        labelFileWaitingForSize: "Boyut hesaplanıyor",
        labelFileSizeNotAvailable: "Boyut kullanılamıyor",
        labelFileLoading: "Yükleniyor",
        labelFileLoadError: "Dosya yüklenemedi",
        labelFileProcessing: "Sunucuya aktarılıyor",
        labelFileProcessingComplete: "Yüklendi",
        labelFileProcessingAborted: "Yükleme iptal edildi",
        labelFileProcessingError: "Yükleme başarısız",
        labelFileProcessingRevertError: "Geri alma başarısız",
        labelFileRemoveError: "Dosya kaldırılamadı",
        labelTapToCancel: "İptal etmek için dokunun",
        labelTapToRetry: "Tekrar denemek için dokunun",
        labelTapToUndo: "Geri almak için dokunun",
        labelButtonRemoveItem: "Kaldır",
        labelButtonAbortItemLoad: "İptal",
        labelButtonRetryItemLoad: "Tekrar dene",
        labelButtonAbortItemProcessing: "İptal",
        labelButtonUndoItemProcessing: "Geri al",
        labelButtonRetryItemProcessing: "Tekrar dene",
        labelButtonProcessItem: "Yükle",
        labelMaxFileSizeExceeded: "Dosya çok büyük",
        labelMaxFileSize: "En fazla {filesize}",
        labelFileTypeNotAllowed: "Dosya türüne izin verilmiyor",
        fileValidateTypeLabelExpectedTypes: "İzin verilen türler: {allTypes}",
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                const payload = new FormData();
                payload.append("csrf_token", form.elements.csrf_token.value);
                payload.append("document_type", form.elements.document_type.value);
                payload.append("entity_type", form.elements.entity_type.value);
                payload.append("entity_id", form.elements.entity_id.value);
                payload.append("filepond", "1");
                payload.append("document_file", file, file.name);

                const request = new XMLHttpRequest();
                request.open("POST", form.action, true);
                request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                request.upload.onprogress = (event) => {
                    progress(event.lengthComputable, event.loaded, event.total);
                };
                request.onload = () => {
                    let response = null;
                    try {
                        response = JSON.parse((request.responseText || "").replace(/^\uFEFF/, ""));
                    } catch (parseError) {
                        error("Sunucu geçersiz bir yanıt döndürdü.");
                        return;
                    }

                    if (request.status >= 200 && request.status < 300 && response.status === "success") {
                        completedUploads += 1;
                        load(String(response.uploaded?.[0]?.id || file.name));
                        return;
                    }

                    error(response.message || "Dosya yüklenemedi.");
                };
                request.onerror = () => error("Sunucu bağlantısı kurulamadı.");
                request.send(payload);

                return {
                    abort: () => {
                        request.abort();
                        abort();
                    }
                };
            }
        }
    });

    const syncState = () => {
        const files = pond.getFiles();
        const readyFiles = files.filter((item) => item.status === FilePond.FileStatus.IDLE);
        const invalidFiles = files.filter((item) => item.status === FilePond.FileStatus.LOAD_ERROR);
        if (submit) {
            submit.disabled = uploadRunning || readyFiles.length === 0 || invalidFiles.length > 0;
        }
        if (!uploadRunning) {
            setStatus(files.length > 0 ? `${files.length} dosya hazır.` : "");
        }
    };

    pond.on("updatefiles", syncState);
    pond.on("addfile", syncState);
    pond.on("removefile", syncState);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!form.reportValidity() || pond.getFiles().length === 0 || uploadRunning) {
            return;
        }

        uploadRunning = true;
        completedUploads = 0;
        syncState();
        setStatus("Dosyalar sunucuya aktarılıyor...");

        try {
            await pond.processFiles();
            const failed = pond.getFiles().some((item) => item.status === FilePond.FileStatus.PROCESSING_ERROR);
            if (failed) {
                setStatus(`${completedUploads} dosya yüklendi. Başarısız dosyaları kontrol edin.`);
                window.KirpiCore?.toast("Bazı dosyalar yüklenemedi.", "warning");
                return;
            }

            const message = `${completedUploads} dosya başarıyla yüklendi.`;
            window.KirpiCore?.persistPendingToast(message, "success");
            window.location.reload();
        } finally {
            uploadRunning = false;
            syncState();
        }
    });

    modalElement?.addEventListener("hidden.bs.modal", () => {
        if (!uploadRunning) {
            pond.removeFiles();
            form.reset();
            setStatus("");
        }
    });
})();
