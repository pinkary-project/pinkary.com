export function formattingToolbar() {
    return {
        format(event, prefix, suffix = prefix, placeholder = "text") {
            const textarea = event.currentTarget
                .closest("[x-data]")
                ?.querySelector('textarea[x-ref="content"]');

            if (!textarea) {
                return;
            }

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.slice(start, end) || placeholder;
            const replacement = `${prefix}${selected}${suffix}`;

            if (
                textarea.maxLength > 0 &&
                textarea.value.length - (end - start) + replacement.length >
                    textarea.maxLength
            ) {
                return;
            }

            textarea.setRangeText(replacement, start, end, "end");
            textarea.dispatchEvent(new Event("input", { bubbles: true }));
            textarea.focus();

            if (start === end) {
                textarea.setSelectionRange(
                    start + prefix.length,
                    start + prefix.length + placeholder.length,
                );
            }
        },

        quote(event) {
            const textarea = event.currentTarget
                .closest("[x-data]")
                ?.querySelector('textarea[x-ref="content"]');

            if (!textarea) {
                return;
            }

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.slice(start, end) || "quoted text";
            const replacement = selected
                .split("\n")
                .map((line) => `> ${line}`)
                .join("\n");

            if (
                textarea.maxLength > 0 &&
                textarea.value.length - (end - start) + replacement.length >
                    textarea.maxLength
            ) {
                return;
            }

            textarea.setRangeText(replacement, start, end, "end");
            textarea.dispatchEvent(new Event("input", { bubbles: true }));
            textarea.focus();
        },

        link(event) {
            const url = window.prompt("Enter the link URL", "https://");

            if (!url || !/^https?:\/\//i.test(url)) {
                return;
            }

            this.format(event, "[", `](${url})`, "link text");
        },
    };
}
