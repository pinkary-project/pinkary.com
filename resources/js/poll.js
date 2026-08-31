const poll = () => ({
    isPoll: false,
    pollOptions: ['', ''],
    pollDuration: 1,

    init() {
        this.isPoll = this.$wire.isPoll;
        this.pollOptions = [...this.$wire.pollOptions];
        this.pollDuration = this.$wire.pollDuration;

        this.$watch('isPoll', (value) => {
            this.$wire.isPoll = value;
            if (!value) {
                this.resetPoll();
            }
        });

        this.$watch('pollOptions', (value) => {
            this.$wire.pollOptions = [...value];
        });

        this.$watch('pollDuration', (value) => {
            this.$wire.pollDuration = value;
        });

        Livewire.on('question.created', () => {
            this.resetPoll();
        });
    },

    togglePoll() {
        this.isPoll = !this.isPoll;
    },

    addPollOption() {
        if (this.pollOptions.length < 4) {
            this.pollOptions.push('');
        }
    },

    removePollOption(index) {
        if (this.pollOptions.length > 2) {
            this.pollOptions.splice(index, 1);
        }
    },

    resetPoll() {
        this.isPoll = false;
        this.pollOptions = ['', ''];
        this.pollDuration = 1;
        this.$wire.isPoll = false;
        this.$wire.pollOptions = ['', ''];
        this.$wire.pollDuration = 1;
    },

    canAddOption() {
        return this.pollOptions.length < 4;
    },

    canRemoveOption() {
        return this.pollOptions.length > 2;
    }
})

export { poll }
