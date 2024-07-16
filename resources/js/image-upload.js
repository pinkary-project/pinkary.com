const imageUpload = () => ({

    uploading: false,
    uploadLimit: 2,
    maxFileSize: 2 * 1024 * 1024,
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
    },

    addErrors(errors) {
        this.errors = [...new Set(this.errors), ...errors].filter(Boolean);
        console.log(this.errors);
    },

    checkFileSize(files) {
        this.errors = [];
        Array.from(files).forEach((file) => {
            if (file.size > this.maxFileSize) {
                const sizeInMb = this.maxFileSize / (1024 * 1024);
                this.addErrors([`${file.name} is too large. Max file size is ${sizeInMb}MB.`]);
            }
        });
        if (this.errors.length === 0) {
            this.handleUploading(files);
        }
    },

    handleUploading(files) {
        if ((files.length + this.images.length) > this.uploadLimit) {
            this.uploading = false;
            // TODO: work out if we want an error or a notification here
            this.addErrors([`Maximum of ${this.uploadLimit} images per post.`]);
            // this.$notify(`Maximum of ${this.uploadLimit} images per post.`, {
            //     wrapperId: 'flashMessageWrapper',
            //     templateId: 'flashMessageTemplate',
            //     autoClose: 3000,
            //     autoRemove: 4000
            // });
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

    insertAtCorrectPosition(content, textarea) {
        textarea.value = textarea.value.replace(
            /Uploading image\.\.\./g,
            ''
        );
        let existingContent = textarea.value;
        if (existingContent && !existingContent.endsWith('\n')) {
            content = '\n' + content;
        }
        textarea.value = existingContent + content;
        this.resizeTextarea(textarea);
    },

    resizeTextarea(textarea) {
        this.$nextTick(() => {
            textarea.resize();
        });
    },

    createMarkdownImage(event) {
        const {path, originalName} = event;
        this.images.push({path, originalName});
        this.insertAtCorrectPosition(
            `![${originalName}](${path})`,
            this.$refs.content
        );
        this.uploading = false;
    }

})

export {imageUpload}
