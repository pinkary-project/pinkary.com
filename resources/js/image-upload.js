const imageUpload = () => ({
    uploading: false,
    uploadLimit: null,
    maxFileSize: null,
    images: [],
    errors: [],
    textarea: null,

    init() {
        this.textarea = this.$el.querySelector('textarea[x-ref="content"]');
        if (this.$refs.imageButton !== undefined) {
            this.setupListeners();
        }
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

        this.textarea.addEventListener('paste', this.handleImagePaste.bind(this));

        Livewire.on('image.uploaded', (event) => {
            this.createMarkdownImage(event);
        });

        Livewire.on('question.created', () => {
            this.images = [];
            this.errors = [];
        });

        Livewire.hook('morph.updated', ({el, component}) => {
            if (this.$el === el) {
                const errors = component.snapshot.memo.errors;
                this.addErrors(errors);
            }
        });
    },

    handleImagePaste(event) {
        // if no files, handle paste event as normal
        if (event.clipboardData.files.length === 0) {
            return;
        }

        // prevent default behavior to avoid pasting the title of the image
        event.preventDefault();

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

    checkFileSize(files) {
        if (files.length) {
            this.errors = [];
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
            this.uploading = true;
            this.$refs.imageUpload.files = files;
            this.$refs.imageUpload.dispatchEvent(new Event('change'));
            this.insertAtCorrectPosition(
                'Uploading image...',
            );
        }
    },

    replaceUploadingText() {
        this.textarea.value = this.textarea.value.replace(
            /Uploading image\.\.\./g,
            ''
        );
    },

    insertAtCorrectPosition(content) {
        this.replaceUploadingText();
        let existingContent = this.textarea.value;
        if (existingContent && !existingContent.endsWith('\n')) {
            content = '\n' + content;
        }
        this.textarea.value = existingContent + content;
        this.resizeTextarea();
    },

    resizeTextarea() {
        this.textarea.dispatchEvent(new Event('input'));
        this.textarea.selectionStart = this.textarea.selectionEnd = this.textarea.value.length;
        this.textarea.focus();
    },

    removeImage(event, index) {
        event.preventDefault();
        this.$wire.deleteImage(
            this.normalizePath(this.images[index].path)
        );
        this.removeMarkdownImage(index);
        this.images.splice(index, 1);
    },

    createMarkdownImage(item) {
        let path, originalName;
        if (item instanceof Object) {
            ({path, originalName} = item);
            this.images.push({path, originalName});
        } else if (typeof item === 'number') {
            ({path, originalName} = this.images[item]);
        }
        this.insertAtCorrectPosition(
            `![${originalName}](${this.normalizePath(path)})`,
        );
        this.uploading = false;
    },

    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },

    removeMarkdownImage(index) {
        let {path, originalName} = this.images[index];
        let regex = new RegExp(
            `!\\[${this.escapeRegExp(originalName)}\\]\\(${this.normalizePath(path)}\\)\\n?`,
            'g'
        );
        this.textarea.value = this.textarea.value.replace(regex, '');
        this.resizeTextarea();
    },

    normalizePath(path) {
        return path.replace(/\/storage\//, '');
    }
})

export { imageUpload }
