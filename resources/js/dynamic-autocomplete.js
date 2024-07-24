/**
 * This functionality must be used in combination with
 * App\Livewire\Concerns\WithDynamicAutocomplete.
 *
 * @see https://www.algolia.com/doc/ui-libraries/autocomplete/solutions/rich-text-box-with-mentions-and-hashtags
 */

export const dynamicAutocomplete = () => ({
    types: null,
    matchedTypes: null,
    showAutocompleteOptions: false,
    textarea: null,
    text: '',
    selectedIndex: 0,
    activeToken: false,

    autocompleteInputBindings: {
        ['@keyup'](event) {
            this.handleInput({
                text: this.$el.value,
                event: event,
            });
        },
        ['@keydown.arrow-up'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.focusResultsUp();
            }
        },
        ['@keydown.arrow-down'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.focusResultsDown();
            }
        },
        ['@keydown.enter'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.select();
            }
        },
        ['@keydown.tab'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.select();
            }
        },
        ['@keydown.escape'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.closeResults();
            }
        },
        ['@click.away'](event) {
            if (this.showAutocompleteOptions) {
                event.preventDefault();
                this.closeResults();
            }
        },
    },

    init() {
        this.textarea = this.$refs.content;
    },

    handleInput(payload) {
        const cursorPosition = payload.event.target.selectionEnd || 0;
        const activeToken = this.getActiveToken(payload.text, cursorPosition);

        if (activeToken === undefined) {
            this.closeResults();
            return;
        }

        if (activeToken?.word === this.activeToken?.word) {
            // Current word is identical to previously parsed word.
            // Happens when the user triggers a keyup that doesn't
            // alter the text, such as a Shift release.
            return;
        }

        this.activeToken = activeToken;

        this.matchedTypes = this.determineTypesByExpression(activeToken?.word);

        if (!this.matchedTypes.length) {
            this.closeResults();
            return;
        }

        this.$wire.$call('setAutocompleteSearchParams', this.matchedTypes, activeToken.word);

        this.text = payload.text;

        this.openResults();
    },

    getActiveToken(input, cursorPosition) {
        const tokenizedQuery = input.split(/[\s\n]/).reduce((acc, word, index) => {
            const previous = acc[index - 1];
            const start = index === 0 ? index : previous.range[1] + 1;
            const end = start + word.length;

            return acc.concat([{word, range: [start, end]}]);
        }, []);

        if (cursorPosition === undefined) {
            return undefined;
        }

        return tokenizedQuery.find(
            ({range}) => range[0] < cursorPosition && range[1] >= cursorPosition
        );
    },

    determineTypesByExpression(word) {
        return Object.keys(this.types).filter(
            (type) => new RegExp(this.types[type].expression).test(word)
        );
    },

    select(replacement) {
        this.$focus.focus(this.textarea);

        this.$nextTick(() => {
            this.replace(replacement ?? this.getReplacementFromSelectedResult());

            this.activeToken = false;

            this.closeResults();
        });
    },

    getReplacementFromSelectedResult() {
        return this.$refs.results.children[this.selectedIndex].dataset.replacement;
    },

    replace(replacement) {
        replacement = this.formatReplacement(replacement);
        this.textarea.value = replacement;
        this.$dispatch('input', replacement);
    },

    formatReplacement(replacement) {
        const [index] = this.activeToken.range;

        return this.replaceAt(
            this.text,
            replacement + ' ',
            index,
            this.activeToken.word.length
        );
    },

    replaceAt(str, replacement, index, length = 0) {
        const prefix = str.substring(0, index);
        const suffix = str.substring(index + length);

        return prefix + replacement + suffix;
    },

    openResults() {
        this.showAutocompleteOptions = true;
    },

    closeResults() {
        this.showAutocompleteOptions = false;
        this.selectedIndex = 0;
    },

    focusResultsUp() {
        if (this.selectedIndex === 0) {
            this.selectedIndex = this.$refs.results.children.length - 1;
        } else {
            this.selectedIndex = Math.max(0, this.selectedIndex - 1);
        }
        this.$nextTick(() => {
            this.$refs.results.children[this.selectedIndex - 1]?.scrollIntoView({
                block: 'nearest',
            });
        })
    },

    focusResultsDown() {
        if (this.selectedIndex === this.$refs.results.children.length - 1) {
            this.selectedIndex = 0;
        } else {
            this.selectedIndex = Math.min(this.$refs.results.children.length - 1, this.selectedIndex + 1);
        }
        this.$nextTick(() => {
            this.$refs.results.children[this.selectedIndex + 1]?.scrollIntoView({
                block: 'nearest',
            });
        })
    },
});
