export const autocomplete = (config) => ({
    types: null,
    matchedTypes: null,
    showAutocompleteOptions: false,
    workingText: '',
    selectedIndex: 0,
    activeToken: false,
    listeners: [],

    init() {
        this.types = config.types;
        this.initListeners();
    },

    initListeners() {
        this.listeners.push(Livewire.on('autocompleteBoundInputKeyup', (payload) => {this.handleInput(payload)}));
        this.listeners.push(Livewire.on('autocompleteBoundInputArrowUp', (event) => this.focusResultsUp()));
        this.listeners.push(Livewire.on('autocompleteBoundInputArrowDown', (event) => this.focusResultsDown()));
        this.listeners.push(Livewire.on('selectAutocomplete', (event) => this.select()));
        this.listeners.push(Livewire.on('closeAutocompletePanel', (event) => this.closeResults()));
    },

    handleInput(payload) {
        const cursorPosition = payload.event.target.selectionEnd || 0;
        const activeToken = this.getActiveToken(payload.content, cursorPosition);

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

        this.workingText = payload.content;

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
        replacement ??= this.getReplacementFromSelectedResult() ?? this.activeToken.word;

        Livewire.dispatch('autocompleteSelected', {
            newValue: this.formatReplacement(replacement)
        })

        this.activeToken = false;

        this.closeResults();
    },

    getReplacementFromSelectedResult() {
        return this.$refs.results.children[this.selectedIndex].dataset.replacement;
    },

    formatReplacement(replacement) {
        const [index] = this.activeToken.range;

        return this.replaceAt(
            this.workingText,
            replacement,
            index,
            this.activeToken.word.length
        );
    },

    replaceAt(str, replacement, index, length = 0) {
        const prefix = str.substring(0, index);
        const suffix = str.substring(index + length);

        if (!suffix?.startsWith(' ')) {
            replacement = replacement + ' ';
        }

        return prefix + replacement + suffix;
    },

    openResults() {
        this.showAutocompleteOptions = true;
        Livewire.dispatch('autocompletePanelShown');
    },

    closeResults() {
        this.showAutocompleteOptions = false;
        this.selectedIndex = 0;
        Livewire.dispatch('autocompletePanelClosed');
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

export const usesAutocomplete = () => ({
    autocompletePanelIsShown: false,
    listeners: [],

    init() {
        Livewire.on('autocompletePanelShown', () => this.autocompletePanelIsShown = true);
        Livewire.on('autocompletePanelClosed', () => this.autocompletePanelIsShown = false);
        Livewire.on('autocompleteSelected', (event) => {
            this.$focus.focus(this.$el);

            this.$nextTick(() => {
                this.$el.value = event.newValue;
                this.$dispatch('input', event.newValue);
            });
        })
    },

    autocompleteInputBindings: {
        ['@keyup.debounce.250ms'](event) {
            Livewire.dispatch('autocompleteBoundInputKeyup', {
                content: this.$el.value,
                event: event,
            })
        },
        ['@keydown.arrow-up'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('autocompleteBoundInputArrowUp')
            }
        },
        ['@keydown.arrow-down'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('autocompleteBoundInputArrowDown')
            }
        },
        ['@keydown.enter'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('selectAutocomplete')
            }
        },
        ['@keydown.tab'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('selectAutocomplete')
            }
        },
        ['@keydown.escape'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('closeAutocompletePanel')
            }
        },
        ['@click.away'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch('closeAutocompletePanel')
            }
        },
    },
});
