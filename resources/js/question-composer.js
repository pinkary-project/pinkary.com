import { imageUpload } from './image-upload.js';
import { poll } from './poll.js';

const questionComposer = (config = {}) => ({
    ...imageUpload(),
    ...poll(),
    draftKey: config.draftKey,
    maxThreadPosts: config.maxThreadPosts,
    content: '',
    threadPosts: [],
    threadPolls: [],

    init() {
        this.content = this.$persist(this.$wire.entangle('content')).as(this.draftKey);
        this.threadPosts = this.$persist(this.$wire.entangle('threadPosts')).as(`${this.draftKey}_posts`);
        this.threadPolls = this.$persist(this.$wire.entangle('threadPolls')).as(`${this.draftKey}_polls`);
        this.uploadLimit = config.uploadLimit;
        this.maxFileSize = config.maxFileSize;
        this.maxContentLength = config.maxContentLength;

        imageUpload().init.call(this);
        poll().init.call(this);
        this.ensureThreadPolls();

        window.addEventListener('post-modal-poll', (event) => {
            if (this.$wire.customDraftKey !== 'post_modal') {
                return;
            }

            this.isPoll = event.detail.isPoll;
            this.pollOptions = event.detail.pollOptions;
            this.pollDuration = event.detail.pollDuration;
            this.threadPolls = event.detail.threadPolls;
            this.images = event.detail.images;
        });
    },

    ensureThreadPolls() {
        while (this.threadPolls.length < this.threadPosts.length) {
            this.threadPolls.push(this.emptyThreadPoll());
        }

        this.threadPolls.splice(this.threadPosts.length);
    },

    hasDraft() {
        if ((this.content || '').trim() !== '') {
            return true;
        }

        return (this.threadPosts || []).some((post) => (post || '').trim() !== '');
    },

    discardDraft() {
        (this.images || []).forEach((image) => {
            this.$wire.deleteImageAfterValidation(this.normalizePath(image.path));
        });

        this.$wire.$errors.clear();
        this.content = '';
        this.threadPosts = [];
        this.threadPolls = [];
        this.images = [];
        this.removeErrors();
    },

    canAddPost() {
        if (this.uploading) {
            return false;
        }

        if ((this.content || '').trim() === '') {
            return false;
        }

        return (this.threadPosts || []).every((post) => (post || '').trim() !== '');
    },

    addPost() {
        if (this.uploading) {
            return;
        }

        if (this.$wire.customDraftKey !== 'post_modal') {
            this.continueToModal();

            return;
        }

        if (! this.canAddPost()) {
            this.focusFirstEmptyPost();

            return;
        }

        if (this.threadPosts.length < this.maxThreadPosts - 1) {
            this.threadPosts.push('');
            this.threadPolls.push(this.emptyThreadPoll());
            this.focusLastPost();
        }
    },

    focusFirstEmptyPost() {
        if ((this.content || '').trim() === '') {
            this.$refs.content?.focus();

            return;
        }

        this.$nextTick(() => {
            const textareas = this.$root.querySelectorAll('[data-thread-post]');
            const index = (this.threadPosts || []).findIndex((post) => (post || '').trim() === '');
            textareas[index]?.focus();
        });
    },

    continueToModal() {
        const content = (this.content || '').trim();
        const threadPosts = (this.threadPosts || []).filter((post) => (post || '').trim() !== '');
        const pollOptions = [...this.pollOptions];
        const threadPolls = this.threadPolls.map((threadPoll) => ({
            ...threadPoll,
            options: [...threadPoll.options],
        }));
        const images = this.images.map((image) => ({ ...image }));

        if (content !== '' || threadPosts.length > 0) {
            this.$wire.$errors.clear();
            this.$wire.dispatch('thread.continue-in-modal', {
                content,
                threadPosts,
                threadPolls,
                isPoll: Boolean(this.isPoll),
                pollOptions,
                pollDuration: this.pollDuration,
                images,
                sourceDraftKey: this.draftKey,
            });
            window.dispatchEvent(new CustomEvent('post-modal-poll', {
                detail: { isPoll: Boolean(this.isPoll), pollOptions, pollDuration: this.pollDuration, threadPolls, images },
            }));

            this.content = '';
            this.threadPosts = [];
            this.isPoll = false;
            this.pollOptions = ['', ''];
            this.pollDuration = 1;
        }

        this.$dispatch('open-modal', 'post-create');
    },

    removePost(index) {
        this.images
            .filter((image) => image.target === index)
            .forEach((image) => {
                this.$wire.deleteImageAfterValidation(this.normalizePath(image.path));
            });

        this.images = this.images
            .filter((image) => image.target !== index)
            .map((image) => image.target !== null && image.target > index
                ? { ...image, target: image.target - 1 }
                : image
            );

        if (this.activeImageTarget === index) {
            this.activeImageTarget = null;
        } else if (this.activeImageTarget !== null && this.activeImageTarget > index) {
            this.activeImageTarget -= 1;
        }

        this.threadPosts.splice(index, 1);
        this.threadPolls.splice(index, 1);
    },

    emptyThreadPoll() {
        return { isPoll: false, options: ['', ''], duration: 1 };
    },

    toggleThreadPoll(index) {
        if (! this.threadPolls[index]) {
            this.threadPolls[index] = this.emptyThreadPoll();
        }

        this.threadPolls[index].isPoll = ! this.threadPolls[index].isPoll;
    },

    addThreadPollOption(index) {
        if (! this.threadPolls[index]) {
            this.threadPolls[index] = this.emptyThreadPoll();
        }

        if (this.threadPolls[index].options.length < 4) {
            this.threadPolls[index].options.push('');
        }
    },

    removeThreadPollOption(index, optionIndex) {
        if (this.threadPolls[index].options.length > 2) {
            this.threadPolls[index].options.splice(optionIndex, 1);
        }
    },

    focusLastPost() {
        this.$nextTick(() => {
            const textareas = this.$root.querySelectorAll('[data-thread-post]');
            const last = textareas[textareas.length - 1];

            if (! last) {
                return;
            }

            last.focus();
            last.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    },
});

export { questionComposer };
