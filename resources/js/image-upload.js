const imageUpload = () => ({
    uploading: false,
    uploadLimit: null,
    maxFileSize: null,
    images: [],
    errors: [],

    init() {
        this.setupListeners();
    },

    setupListeners() {
        this.$refs.imageButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.$refs.imageInput.click();
        });

        this.$refs.imageInput.addEventListener('change', (event) => {
            this.checkFileSize(event.target.files);
        });

        Livewire.on('image.uploaded', (event) => {
            this.createMarkdownImage(event);
        });

        Livewire.on('question.created', () => {
            this.images = [];
            this.errors = [];
        });

        this.$watch('errors', (value) => {
            if (value.length) {
                this.uploading = false;
                this.replaceUploadingText(this.$refs.content);
            }
        });

        Livewire.hook('commit', (event) => {
            event.succeed(() => {
                this.resizeTextarea(this.$refs.content);
            });
        });
    },

    addErrors(errors) {
        this.errors = [...new Set(this.errors), ...errors].filter(Boolean);
    },

    checkFileSize(files) {
        if (files.length) {
            this.errors = [];
            Array.from(files).forEach((file) => {
                if ((file.size / 1024) > this.maxFileSize) {
                    const sizeInMb = (this.maxFileSize / 1024).toFixed(0)
                    this.addErrors([`${file.name} is too large. Max file size is ${sizeInMb}MB.`]);
                }
            });
            if (this.errors.length === 0) {
                this.handleUploading(files);
            }
        }
    },

    handleUploading(files) {
        if ((files.length + this.images.length) > this.uploadLimit) {
            this.uploading = false;
            this.addErrors([`You can only upload ${this.uploadLimit} images.`]);
        } else {
            this.uploading = true;
            this.$refs.imageUpload.files = files;
            this.$refs.imageUpload.dispatchEvent(new Event('change'));
            this.insertAtCorrectPosition(
                'Uploading image...',
                this.$refs.content
            );
        }
    },

    replaceUploadingText(textarea) {
        textarea.value = textarea.value.replace(
            /Uploading image\.\.\./g,
            ''
        );
    },

    insertAtCorrectPosition(content, textarea) {
        this.replaceUploadingText(textarea);
        let existingContent = textarea.value;
        if (existingContent && !existingContent.endsWith('\n')) {
            content = '\n' + content;
        }
        textarea.value = existingContent + content;
        this.resizeTextarea(textarea);
    },

    resizeTextarea(textarea) {
        this.$nextTick(() => {
            textarea.dispatchEvent(new Event('input'));
            textarea.resize();
            textarea.selectionStart = textarea.selectionEnd = textarea.value.length;
            textarea.focus();
        });
    },

    removeImage(event, index) {
        event.preventDefault();
        this.$dispatch('image.delete', { image: this.images[index] });
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
            this.$refs.content
        );
        this.uploading = false;
    },

    removeMarkdownImage(index) {
        let {path, originalName} = this.images[index];
        let textarea = this.$refs.content;
        let content = textarea.value;
        let regex = new RegExp(`!\\[${originalName}\\]\\(${this.normalizePath(path)}\\)\\n?`, 'g');
        this.$refs.content.value = content.replace(regex, '');
        this.resizeTextarea(textarea);
    },

    normalizePath(path) {
        return path.replace(/\/storage\//, '');
    }
})

export { imageUpload }
