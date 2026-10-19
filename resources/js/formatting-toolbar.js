function replaceSelection(event, placeholder, buildReplacement) {
    const textarea = event.currentTarget
        .closest("[x-data]")
        ?.querySelector('textarea[x-ref="content"]');

    if (!textarea) {
        return null;
    }

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const replacement = buildReplacement(
        textarea.value.slice(start, end) || placeholder,
    );

    if (
        textarea.maxLength > 0 &&
        textarea.value.length - (end - start) + replacement.length >
            textarea.maxLength
    ) {
        return null;
    }

    textarea.setRangeText(replacement, start, end, "end");
    textarea.dispatchEvent(new Event("input", { bubbles: true }));
    textarea.focus();

    return { textarea, start, end };
}

export function formattingToolbar() {
    return {
        format(event, prefix, suffix = prefix, placeholder = "text") {
            const result = replaceSelection(
                event,
                placeholder,
                (selected) => `${prefix}${selected}${suffix}`,
            );

            if (result && result.start === result.end) {
                result.textarea.setSelectionRange(
                    result.start + prefix.length,
                    result.start + prefix.length + placeholder.length,
                );
            }
        },

        quote(event) {
            replaceSelection(event, "quoted text", (selected) =>
                selected
                    .split("\n")
                    .map((line) => `> ${line}`)
                    .join("\n"),
            );
        },

        link(event) {
            let url = window.prompt("Enter the link URL", "https://")?.trim();

            if (!url) {
                return;
            }

            if (!/^https?:\/\//i.test(url)) {
                url = `https://${url}`;
            }

            this.format(event, "[", `](${url})`, "link text");
        },
    };
}
