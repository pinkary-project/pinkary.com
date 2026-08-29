const imageUpload = () => ({
    uploading: false,
    uploadLimit: null,
    maxFileSize: null,
    maxContentLength: null,
    images: [],
    errors: [],
    textarea: null,
    activeImageTarget: null,

    init() {
        this.textarea = this.$el.querySelector('textarea[x-ref="content"]');
        if (this.$refs.imageButton !== undefined) {
            this.setupListeners();
        }
    },

    /**
     * The field images are inserted into: the main post (null) or a thread post (index).
     */
    activeField() {
        if (this.activeImageTarget === null || this.activeImageTarget === undefined) {
            return {
                textarea: this.$el.querySelector('textarea[x-ref="content"]'),
                prop: 'content',
            };
        }

        return {
            textarea: this.$el.querySelectorAll('[data-thread-post]')[this.activeImageTarget] ?? null,
            prop: 'threadPosts.' + this.activeImageTarget,
        };
    },

    allFields() {
        const fields = [{
            textarea: this.$el.querySelector('textarea[x-ref="content"]'),
            prop: 'content',
        }];

        this.$el.querySelectorAll('[data-thread-post]').forEach((textarea, index) => {
            fields.push({ textarea, prop: 'threadPosts.' + index });
        });

        return fields;
    },

    syncField(field) {
        if (this.$wire && field.textarea) {
            this.$wire.set(field.prop, field.textarea.value, false);
        }
    },

    syncAllFields() {
        this.allFields().forEach((field) => {
            this.syncField(field);
        });
    },

    setupListeners() {
        this.$refs.imageButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.$refs.imageInput.click();
        });

        this.$refs.imageInput.addEventListener('change', (event) => {
            this.checkFileSize(event.target.files);
            event.target.value = '';
        });

        this.textarea.addEventListener('paste', (event) => {
            this.handleImagePaste(event);
        });

        const registerListeners = (target) => {
            target.on('image.uploaded', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : (payload?.detail ?? payload);
                this.createMarkdownImage(data);
            });

            target.on('question.created', () => {
                this.images = [];
                this.removeErrors();
            });
        };

        if (this.$wire) {
            registerListeners(this.$wire);
        } else {
            registerListeners(Livewire);
        }

        Livewire.interceptMessage(({ component, message, onSuccess }) => {
            onSuccess(({ onMorph }) => {
                onMorph(async () => {
                    if (this.$el === message.el) {
                        const errors = message && message.memo && message.memo.errors ? message.memo.errors : [];
                        this.addErrors(errors);
                    }
                });
            });
        });
    },

    handleImagePaste(event) {
        // if no files, handle paste event as normal
        if (event.clipboardData.files.length === 0) {
            return;
        }

        // don't allow multiple uploads at once
        if (this.uploading) {
            return;
        }

        // build out the file list from the clipboard, filtering only for images.
        const dataTransfer = new DataTransfer();
        for (const item of event.clipboardData.files) {
            if (!item.type.startsWith('image/')) {
                this.addErrors(['The file must be an image.']);
                return;
            }

            dataTransfer.items.add(item);
        }

        this.checkFileSize(dataTransfer.files);
    },

    addErrors(errors) {
        this.$nextTick(() => {
            const incomingErrors = Object.values(errors).flat()
            const uniqueErrors = new Set([...this.errors, ...incomingErrors]);
            this.errors = Array.from(uniqueErrors);
            this.uploading = false;
            this.replaceUploadingText();
            this.resizeTextarea();
        });
    },

    removeErrors() {
        this.errors = [];
    },

    checkFileSize(files) {
        if (files.length) {
            this.removeErrors();
            Array.from(files).forEach((file) => {
                if ((file.size / 1024) > this.maxFileSize) {
                    this.addErrors([`The image may not be greater than ${this.maxFileSize} kilobytes.`]);
                }
            });
            if (this.errors.length === 0) {
                this.handleUploading(files);
            }
        }
    },

    handleUploading(files) {
        if ((files.length + this.images.length) > this.uploadLimit) {
            this.addErrors([`You can only upload ${this.uploadLimit} images.`]);
        } else {
            // Queue all draft fields before the upload request can rehydrate the component.
            this.syncAllFields();
            this.uploading = true;
            this.$refs.imageUpload.files = files;
            this.$refs.imageUpload.dispatchEvent(new Event('change'));
            this.insertAtCorrectPosition(
                'Uploading image...',
            );
        }
    },

    replaceUploadingText() {
        const field = this.activeField();

        if (! field.textarea) {
            return;
        }

        field.textarea.value = field.textarea.value.replace(
            /Uploading image\.\.\./g,
            ''
        );
        this.syncField(field);
    },

    insertAtCorrectPosition(content) {
        this.replaceUploadingText();

        const field = this.activeField();

        if (! field.textarea) {
            return;
        }

        let existingContent = field.textarea.value;
        if (existingContent && !existingContent.endsWith('\n')) {
            content = '\n' + content;
        }
        field.textarea.value = existingContent + content;
        this.syncField(field);
        this.resizeTextarea();
    },

    resizeTextarea() {
        const field = this.activeField();

        if (! field.textarea) {
            return;
        }

        field.textarea.dispatchEvent(new Event('input', { bubbles: true }));
        field.textarea.dispatchEvent(new Event('change', { bubbles: true }));
        field.textarea.selectionStart = field.textarea.selectionEnd = field.textarea.value.length;
        field.textarea.focus();
    },

    removeImage(event, index) {
        event.preventDefault();
        this.$wire.deleteImageAfterValidation(
            this.normalizePath(this.images[index].path)
        );
        this.removeMarkdownImage(index);
        this.images.splice(index, 1);
        this.removeErrors();
    },

    /**
     * Inserts image markdown into the active textarea.
     * Expected payload shape: { path: string, originalName: string } (dispatched on image.uploaded)
     * or a number index (when re-inserting an existing uploaded image from the list).
     */
    createMarkdownImage(payload) {
        let path, originalName;

        if (typeof payload === 'number') {
            if (!this.images[payload]) {
                return;
            }
            ({ path, originalName } = this.images[payload]);
        } else if (payload && typeof payload === 'object') {
            path = payload.path;
            originalName = payload.originalName;

            if (path && originalName && !this.images.some((img) => img.path === path)) {
                this.images.push({ path, originalName });
            }
        }

        if (!path || !originalName) {
            this.replaceUploadingText();
            this.uploading = false;

            return;
        }

        const normalizedPath = this.normalizePath(path);
        const markdownSnippet = `![${originalName}](${normalizedPath})`;
        const field = this.activeField();

        if (! field.textarea) {
            this.replaceUploadingText();
            this.uploading = false;

            return;
        }

        if (field.textarea.value.includes(markdownSnippet)) {
            this.replaceUploadingText();
            this.uploading = false;

            return;
        }

        if (this.isExceedingMaxContentLength(markdownSnippet)) {
            this.addErrors(['Adding this image will exceed the maximum content length.']);

            return;
        }

        if (this.errors.length > 0) {
            this.removeErrors();
        }

        this.insertAtCorrectPosition(markdownSnippet);
        this.uploading = false;
    },

    isExceedingMaxContentLength(markdownSnippet) {
        const field = this.activeField();

        if (! field.textarea) {
            return false;
        }

        const newLength = field.textarea.value.length + markdownSnippet.length;

        return newLength > this.maxContentLength;
    },

    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },

    removeMarkdownImage(index) {
        if (!this.images[index]) {
            return;
        }

        let { path, originalName } = this.images[index];
        let regex = new RegExp(
            `!\\[${this.escapeRegExp(originalName)}\\]\\(${this.normalizePath(path)}\\)\\n?`,
            'g'
        );

        this.allFields().forEach((field) => {
            if (! field.textarea || ! regex.test(field.textarea.value)) {
                regex.lastIndex = 0;

                return;
            }

            regex.lastIndex = 0;
            field.textarea.value = field.textarea.value.replace(regex, '');
            this.syncField(field);
            field.textarea.dispatchEvent(new Event('input', { bubbles: true }));
        });

        this.textarea?.focus();
    },

    normalizePath(path) {
        if (!path || typeof path !== 'string') {
            return '';
        }

        return path.includes('/images/') ? path.substring(path.indexOf('images/')) : path;
    }
})

export { imageUpload }
