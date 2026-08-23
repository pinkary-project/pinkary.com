/**
 * Remove a question card from the page when it is ignored, collapsing its
 * list-item wrapper (marked with "data-question-item") when no cards remain.
 */
export function questionItem() {
    window.addEventListener('question.ignored', (event) => {
        const card = document.getElementById(`q-${event.detail.questionId}`);

        if (card === null) {
            return;
        }

        const item = card.closest('[data-question-item]');

        card.remove();

        if (item !== null && item.querySelector('article') === null) {
            item.remove();
        }
    });
}
