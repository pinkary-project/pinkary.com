export const autocomplete = (config) => ({
    componentId: null,
    types: null,
    matchedTypes: null,
    showAutocompleteOptions: false,
    workingText: '',
    selectedIndex: 0,
    activeToken: false,
    activeInputId: null,
    pendingInput: null,
    processingInput: false,
    listeners: [],

    init() {
        this.componentId = config.componentId;
        this.types = config.types;
        this.initListeners();
    },

    destroy() {
        this.listeners.forEach((removeListener) => removeListener());
    },

    initListeners() {
        this.listeners.push(Livewire.on(`${this.componentId}:autocompleteBoundInputKeyup`, (payload) => this.handleInput(payload)));
        this.listeners.push(Livewire.on(`${this.componentId}:autocompleteBoundInputArrowUp`, () => this.focusResultsUp()));
        this.listeners.push(Livewire.on(`${this.componentId}:autocompleteBoundInputArrowDown`, () => this.focusResultsDown()));
        this.listeners.push(Livewire.on(`${this.componentId}:selectAutocomplete`, () => this.select()));
        this.listeners.push(Livewire.on(`${this.componentId}:closeAutocompletePanel`, () => this.closeResults()));
    },

    handleInput(payload) {
        this.pendingInput = payload;

        if (this.processingInput) {
            return;
        }

        this.processPendingInput();
    },

    async processPendingInput() {
        this.processingInput = true;

        while (this.pendingInput !== null) {
            const payload = this.pendingInput;
            this.pendingInput = null;

            await this.processInput(payload);
        }

        this.processingInput = false;
    },

    async processInput(payload) {
        this.activeInputId = payload.inputId;

        const activeToken = this.getActiveToken(payload.content, payload.cursorPosition);

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

        this.workingText = payload.content;

        await this.$wire.$call('setAutocompleteSearchParams', this.matchedTypes, activeToken.word);

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

        Livewire.dispatch(`${this.componentId}:autocompleteSelected`, {
            newValue: this.formatReplacement(replacement),
            inputId: this.activeInputId,
        });

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
        Livewire.dispatch(`${this.componentId}:autocompletePanelShown`);
    },

    closeResults() {
        this.showAutocompleteOptions = false;
        this.selectedIndex = 0;
        Livewire.dispatch(`${this.componentId}:autocompletePanelClosed`);
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

export const usesAutocomplete = (componentId) => ({
    autocompletePanelIsShown: false,
    listeners: [],

    init() {
        this.listeners.push(Livewire.on(`${componentId}:autocompletePanelShown`, () => this.autocompletePanelIsShown = true));
        this.listeners.push(Livewire.on(`${componentId}:autocompletePanelClosed`, () => this.autocompletePanelIsShown = false));
        this.listeners.push(Livewire.on(`${componentId}:autocompleteSelected`, (event) => {
            if (event.inputId !== this.$el.id) {
                return;
            }

            this.$focus.focus(this.$el);

            this.$nextTick(() => {
                this.$el.value = event.newValue;
                this.$dispatch('input', event.newValue);
            });
        }));
    },

    destroy() {
        this.listeners.forEach((removeListener) => removeListener());
    },

    autocompleteInputBindings: {
        ['@keyup.debounce.250ms']() {
            Livewire.dispatch(`${componentId}:autocompleteBoundInputKeyup`, {
                content: this.$el.value,
                cursorPosition: this.$el.selectionEnd || 0,
                inputId: this.$el.id,
            });
        },
        ['@keydown.arrow-up'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:autocompleteBoundInputArrowUp`);
            }
        },
        ['@keydown.arrow-down'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:autocompleteBoundInputArrowDown`);
            }
        },
        ['@keydown.enter'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:selectAutocomplete`);
            }
        },
        ['@keydown.tab'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:selectAutocomplete`);
            }
        },
        ['@keydown.escape'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:closeAutocompletePanel`);
            }
        },
        ['@click.away'](event) {
            if (this.autocompletePanelIsShown) {
                event.preventDefault();
                Livewire.dispatch(`${componentId}:closeAutocompletePanel`);
            }
        },
    },
});
