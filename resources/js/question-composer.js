import { imageUpload } from './image-upload.js';
import { poll } from './poll.js';
import autosize from 'autosize';

const questionComposer = (config = {}) => ({
    ...imageUpload(),
    ...poll(),
    draftKey: config.draftKey,
    maxThreadPosts: config.maxThreadPosts,
    compactComposer: config.compact === true,
    hasInteracted: false,
    content: '',
    threadPosts: [],
    threadPolls: [],

    init() {
        this.content = typeof this.$wire.$get('content') === 'string' ? this.$wire.$get('content') : '';
        this.threadPosts = Array.isArray(this.$wire.$get('threadPosts')) ? this.$wire.$get('threadPosts') : [];
        this.threadPolls = Array.isArray(this.$wire.$get('threadPolls')) ? this.$wire.$get('threadPolls') : [];
        this.$watch('content', (value) => this.$wire.$set('content', value, false));
        this.$watch('threadPosts', (value) => this.$wire.$set('threadPosts', value, false));
        this.$watch('threadPolls', (value) => this.$wire.$set('threadPolls', value, false));
        this.persistDraft('', () => this.content, (value) => {
            this.content = typeof value === 'string' ? value : '';
        });
        this.persistDraft('posts', () => this.threadPosts, (value) => {
            this.threadPosts = Array.isArray(value) ? value : [];
        });
        this.persistDraft('polls', () => this.threadPolls, (value) => {
            this.threadPolls = Array.isArray(value) ? value : [];
        });
        this.uploadLimit = config.uploadLimit;
        this.maxFileSize = config.maxFileSize;
        this.maxContentLength = config.maxContentLength;
        this.hasInteracted = !this.compactComposer || this.hasDraft();

        imageUpload().init.call(this);
        poll().init.call(this);
        this.ensureThreadPolls();
        this.resizeAllTextareas();

        Livewire.on('question.created', () => {
            this.content = '';
            this.threadPosts = [];
            this.threadPolls = [];
            this.images = [];
            this.hasInteracted = !this.compactComposer;
            this.$wire.$errors.clear();
        });

        this.$wire.interceptMessage(({ onSuccess }) => {
            onSuccess(({ onMorph }) => {
                onMorph(() => {
                    this.resizeAllTextareas();
                });
            });
        });

        window.addEventListener('post-modal-poll', (event) => {
            if (this.$wire.customDraftKey !== 'post_modal') {
                return;
            }

            this.isPoll = event.detail.isPoll;
            this.pollOptions = event.detail.pollOptions;
            this.pollDuration = event.detail.pollDuration;
            this.content = event.detail.content || '';
            this.threadPosts = event.detail.threadPosts || [];
            this.threadPolls = event.detail.threadPolls || [];
            this.images = event.detail.images || [];
            this.$wire.$set('imageSourceDraftKey', event.detail.sourceDraftKey || null, false);
            if (event.detail.channelId) {
                this.$wire.$set('channelId', event.detail.channelId, false);
                this.$nextTick(() => {
                    window.dispatchEvent(new CustomEvent('channel-selected', {
                        detail: { id: event.detail.channelId, name: event.detail.channelName || '' },
                    }));
                });
            }
            this.ensureThreadPolls();
            this.resizeAllTextareas();
            this.scrollComposerToBottom();
        });
    },

    resizeAllTextareas() {
        this.$nextTick(() => {
            requestAnimationFrame(() => {
                this.$root.querySelectorAll('textarea').forEach((textarea) => {
                    autosize.update(textarea);
                });
            });
        });
    },

    persistDraft(suffix, get, set) {
        Alpine.persist(`${this.draftKey}${suffix ? `_${suffix}` : ''}`, { get, set });
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

    expandComposer() {
        this.hasInteracted = true;
    },

    showSecondaryControls() {
        return !this.compactComposer
            || this.hasInteracted
            || this.hasDraft()
            || this.images.length > 0
            || this.isPoll
            || Boolean(this.$wire.channelId);
    },

    discardDraft() {
        this.$wire.discardSourceImages();

        (this.images || []).forEach((image) => {
            this.$wire.deleteImageAfterValidation(this.normalizePath(image.path));
        });

        this.$wire.$errors.clear();
        this.content = '';
        this.threadPosts = [];
        this.threadPolls = [];
        this.images = [];
        this.$wire.$set('channelId', null, false);
        const channelComponent = this.$el ? this.$el.querySelector('[data-channel-picker]') : null;
        if (channelComponent) {
            channelComponent.removeAttribute('data-selected-id');
            channelComponent.removeAttribute('data-selected-name');
        }
        window.dispatchEvent(new CustomEvent('channel-selected', { detail: null }));
        this.removeErrors();
    },

    canAddPost() {
        if (this.uploading) {
            return false;
        }

        if ((typeof this.content === 'string' ? this.content : '').trim() === '') {
            return false;
        }

        return (Array.isArray(this.threadPosts) ? this.threadPosts : []).every((post) =>
            (typeof post === 'string' ? post : '').trim() !== '',
        );
    },

    addPost() {
        if (this.uploading) {
            return;
        }

        if (this.$wire.customDraftKey !== 'post_modal') {
            if (this.threadPosts.length < this.maxThreadPosts - 1) {
                this.threadPosts.push('');
                this.threadPolls.push(this.emptyThreadPoll());
                this.continueToModal(true);
            }

            return;
        }

        if (! this.canAddPost()) {
            this.focusFirstEmptyPost();

            return;
        }

        if (this.threadPosts.length < this.maxThreadPosts - 1) {
            this.threadPosts.push('');
            this.threadPolls.push(this.emptyThreadPoll());
            this.scrollComposerToBottom();
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

    scrollComposerToBottom() {
        this.$nextTick(() => {
            requestAnimationFrame(() => {
                this.$refs.composerScroll?.scrollTo({
                    top: this.$refs.composerScroll.scrollHeight,
                    behavior: 'smooth',
                });
            });
        });
    },

    continueToModal(includeEmptyPost = false) {
        const content = (this.content || '').trim();
        const threadPosts = includeEmptyPost
            ? [...this.threadPosts]
            : (this.threadPosts || []).filter((post) => (post || '').trim() !== '');
        const pollOptions = [...this.pollOptions];
        const threadPolls = this.threadPolls.map((threadPoll) => ({
            ...threadPoll,
            options: [...threadPoll.options],
        }));
        const images = this.images.map((image) => ({ ...image }));

        if (content !== '' || threadPosts.length > 0) {
            this.$wire.$errors.clear();

            let channelId = this.$wire.channelId ? parseInt(this.$wire.channelId, 10) : null;
            let channelName = '';

            const channelComponent = this.$el ? this.$el.querySelector('[data-channel-picker]') : null;
            if (channelComponent) {
                const rawId = channelComponent.getAttribute('data-selected-id');
                const rawName = channelComponent.getAttribute('data-selected-name');
                if (rawId) {
                    channelId = parseInt(rawId, 10);
                }
                if (rawName) {
                    channelName = rawName;
                }

                if (window.Alpine) {
                    const channelState = window.Alpine.$data ? window.Alpine.$data(channelComponent) : channelComponent._x_dataStack?.[0];
                    if (channelState) {
                        channelId = channelState.selectedChannelId ? parseInt(channelState.selectedChannelId, 10) : channelId;
                        channelName = channelState.selectedChannelName || channelName;
                        if (! channelName && channelId && channelState.channels) {
                            const found = channelState.channels.find((c) => String(c.id) === String(channelId));
                            if (found) {
                                channelName = found.name;
                            }
                        }
                    }
                }
            }

            window.dispatchEvent(new CustomEvent('post-modal-poll', {
                detail: {
                    content,
                    threadPosts,
                    isPoll: Boolean(this.isPoll),
                    pollOptions,
                    pollDuration: this.pollDuration,
                    threadPolls,
                    images,
                    sourceDraftKey: this.draftKey,
                    channelId,
                    channelName,
                },
            }));

            this.content = '';
            this.threadPosts = [];
            this.threadPolls = [];
            this.isPoll = false;
            this.pollOptions = ['', ''];
            this.pollDuration = 1;
            this.images = [];
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
