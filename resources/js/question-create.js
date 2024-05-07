const questionCreate = ({ mentionSuggestionsSearch }) => ({

    showMentionSuggestions: false,

    mentionStartOffset: null,

    mentionSuggestionsSearch,

    highlightedMentionSuggestionIndex: 0,

    mentionSuggestions: [],

    handleContentUpdate() {
        if (this.showMentionSuggestions) {
            this.syncMentionSearch();
            return;
        }

        const caretPosition = this.$refs.content.selectionStart;
        const textBeforeCaret = this.$refs.content.value.slice(
            0,
            caretPosition
        );

        if (textBeforeCaret.endsWith('@')) {
            this.showMentionSuggestions = true;
            this.mentionStartOffset = caretPosition - 1;
        }
    },

    syncMentionSearch() {
        const caretPosition = this.$refs.content.selectionStart;
        const fullMentionSearch = this.$refs.content.value.slice(
            this.mentionStartOffset,
            caretPosition
        );

        // If the user deletes the first character after the @ symbol,
        // we should exit early so that they can continue typing.
        if (fullMentionSearch === '@') {
            return;
        }

        if (! /@[a-z0-9_]+$/gi.test(fullMentionSearch)) {
            this.hideMentionSuggestions();
            return;
        }

        this.mentionSuggestions = [];
        this.mentionSuggestionsSearch = fullMentionSearch.slice(1);
        this.highlightedMentionSuggestionIndex = 0;
    },

    hideMentionSuggestions() {
        this.showMentionSuggestions = false;
        this.mentionStartOffset = null;
        this.mentionSuggestionsSearch = '';
    },

    handleMentionSuggestionsKeyboardInput($event) {
        if (! this.showMentionSuggestions) {
            return;
        }

        if (! ['Escape', 'ArrowDown', 'ArrowUp', 'Enter'].includes($event.key)) {
            return;
        }

        console.log(this.mentionSuggestions.length);

        $event.preventDefault();

        if ($event.key === 'Escape') {
            this.hideMentionSuggestions();
        }

        if ($event.key === 'ArrowDown') {
            this.highlightedMentionSuggestionIndex = Math.min(this.mentionSuggestions.length - 1, this.highlightedMentionSuggestionIndex + 1)
            this.scrollMentionSuggestionsIntoView();
        }

        if ($event.key === 'ArrowUp') {
            this.highlightedMentionSuggestionIndex = Math.max(0, this.highlightedMentionSuggestionIndex - 1);
            this.scrollMentionSuggestionsIntoView();
        }

        if ($event.key === 'Enter') {
            this.insertMentionSuggestion(this.getChosenMentionSuggestion());
            this.hideMentionSuggestions();
        }
    },

    scrollMentionSuggestionsIntoView() {
        this.$nextTick(() => {
            this.$refs.mentionSuggestionsList.children[this.highlightedMentionSuggestionIndex].scrollIntoView({
                block: "nearest",
                inline: "start",
            });
        })
    },

    insertMentionSuggestion(username) {
        const content = this.$refs.content.value;
        const mentionStart = content.slice(0, this.mentionStartOffset);
        const mentionEnd = content.slice(this.$refs.content.selectionStart);

        this.$refs.content.value = `${mentionStart}@${username} ${mentionEnd}`;
    },

    getChosenMentionSuggestion() {
        return this.mentionSuggestions[this.highlightedMentionSuggestionIndex];
    },
})

const mentionSuggestionItem = ({ index, username }) => ({
    init() {
        this.mentionSuggestions[index] = username;
    },

    onClick() {
        this.insertMentionSuggestion(username);
    },

    onMouseover() {
        this.highlightedMentionSuggestionIndex = index;
    },
})

export { questionCreate, mentionSuggestionItem }
