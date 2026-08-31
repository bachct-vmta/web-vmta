/**
 * Custom CKEditor 5 upload adapter — POSTs to the Laravel media upload endpoint.
 */
class CKEditorUploadAdapter {
    constructor(loader, uploadUrl, csrfToken) {
        this.loader   = loader;
        this.uploadUrl = uploadUrl;
        this.csrfToken = csrfToken;
        this.xhr       = null;
    }

    upload() {
        return this.loader.file.then(file => new Promise((resolve, reject) => {
            this.xhr = new XMLHttpRequest();
            this.xhr.open('POST', this.uploadUrl, true);
            this.xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
            this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            this.xhr.onload = () => {
                if (this.xhr.status >= 200 && this.xhr.status < 300) {
                    try {
                        const data = JSON.parse(this.xhr.responseText);
                        if (data.url) {
                            resolve({ default: data.url });
                        } else {
                            reject(data.message || 'Upload failed');
                        }
                    } catch (e) {
                        reject('Invalid server response');
                    }
                } else {
                    reject(this.xhr.statusText || 'Upload failed');
                }
            };

            this.xhr.onerror  = () => reject('Network error during upload');
            this.xhr.onabort  = () => reject('Upload aborted');

            this.xhr.upload.onprogress = evt => {
                if (evt.lengthComputable) {
                    this.loader.uploadTotal = evt.total;
                    this.loader.uploaded    = evt.loaded;
                }
            };

            const formData = new FormData();
            formData.append('file', file);
            this.xhr.send(formData);
        }));
    }

    abort() {
        if (this.xhr) this.xhr.abort();
    }
}

window.CKEditorUploadAdapter = CKEditorUploadAdapter;
