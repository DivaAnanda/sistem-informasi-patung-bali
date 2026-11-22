const NOTES_DELAY_MS = 900;
const RETRY_DELAY_MS = 4000;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function fill(template, values) {
    return Object.entries(values).reduce((text, [key, value]) => text.replace(`__${key}__`, value), template);
}

function setPressed(button, pressed) {
    button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
}

function isRetryable(status) {
    return status === null || status === 429 || status >= 500;
}

function createSaver(panel, onSaved) {
    const status = panel.querySelector('[data-review-status]');
    let inFlight = false;
    let queued = null;
    let retryTimer = null;

    function flush() {
        clearTimeout(retryTimer);
        retryTimer = null;
        if (inFlight || queued === null) {
            return;
        }
        const payload = queued;
        queued = null;
        send(payload);
    }

    async function send(payload) {
        inFlight = true;
        panel.setAttribute('aria-busy', 'true');
        let failedStatus;
        try {
            const response = await fetch(panel.dataset.url, {
                method: 'PUT',
                keepalive: true,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload),
            });
            if (response.ok) {
                const result = await response.json();
                status.textContent = fill(panel.dataset.savedLabel, { TIME: result.saved_at });
                onSaved(result);
            } else {
                failedStatus = response.status;
            }
        } catch {
            failedStatus = null;
        }
        inFlight = false;
        panel.removeAttribute('aria-busy');

        if (failedStatus === undefined) {
            flush();
            return;
        }
        // A dropped connection must not lose the informant's click: keep the change (newer edits win) and retry.
        if (isRetryable(failedStatus)) {
            status.textContent = panel.dataset.retryLabel;
            queued = { ...payload, ...queued };
            retryTimer = setTimeout(flush, RETRY_DELAY_MS);
        } else {
            status.textContent = panel.dataset.errorLabel;
            flush();
        }
    }

    window.addEventListener('online', flush);

    // Saves run one at a time so a slow request can never overwrite a newer choice;
    // changes made meanwhile are merged into one follow-up request.
    return {
        save(payload) {
            queued = { ...queued, ...payload };
            flush();
        },
        get busy() {
            return inFlight || queued !== null;
        },
    };
}

function renderList(panel, items, query, filter) {
    const list = panel.querySelector('[data-review-search-list]');
    const term = query.trim().toLowerCase();
    const visible = items.filter((item) => {
        const matches = `${item.name} ${item.place}`.toLowerCase().includes(term);
        const status = filter === 'all' || (filter === 'done' ? item.reviewed : !item.reviewed);
        return matches && status;
    });

    list.replaceChildren(
        ...visible.map((item) => {
            const row = document.createElement('li');
            const link = document.createElement('a');
            const name = document.createElement('span');
            const meta = document.createElement('span');
            const badge = document.createElement('span');

            link.href = item.url;
            link.className = 'flex items-center justify-between gap-3 px-1 py-2 hover:bg-stone-50';
            if (item.current) {
                link.setAttribute('aria-current', 'page');
                link.classList.add('font-semibold');
            }
            name.className = 'min-w-0';
            name.textContent = item.name;
            meta.className = 'block text-xs font-normal text-stone-500';
            meta.textContent = item.place;
            name.append(meta);
            badge.className = item.reviewed
                ? 'shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand-800'
                : 'shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600';
            badge.textContent = item.reviewed ? `✓ ${panel.dataset.doneLabel}` : panel.dataset.todoLabel;
            link.append(name, badge);
            row.append(link);
            return row;
        }),
    );
}

function initSearch(panel, items) {
    const input = panel.querySelector('[data-review-search-input]');
    const filters = panel.querySelectorAll('[data-review-filter]');
    let filter = 'all';
    const update = () => renderList(panel, items, input.value, filter);

    input.addEventListener('input', update);
    filters.forEach((button) =>
        button.addEventListener('click', () => {
            filter = button.dataset.reviewFilter;
            filters.forEach((other) => setPressed(other, other === button));
            update();
        }),
    );
    update();

    return update;
}

function initPanel(panel) {
    const themeButtons = [...panel.querySelectorAll('[data-review-theme]')];
    const notes = panel.querySelector('[data-review-notes]');
    const items = JSON.parse(panel.querySelector('[data-review-items]').textContent);
    const refreshList = initSearch(panel, items);
    let notesTimer = null;

    const chosenThemes = () => ({
        themes: themeButtons.filter((button) => button.getAttribute('aria-pressed') === 'true').map((button) => button.dataset.reviewTheme),
    });

    const saver = createSaver(panel, (result) => {
        panel.querySelector('[data-review-progress]').textContent = fill(panel.dataset.progressLabel, {
            DONE: result.progress.done,
            TOTAL: result.progress.total,
        });
        panel.querySelector('[data-review-bar]').style.width = `${(result.progress.done / Math.max(result.progress.total, 1)) * 100}%`;
        const current = items.find((item) => item.current);
        if (current) {
            current.reviewed = result.reviewed;
            refreshList();
        }
    });

    themeButtons.forEach((button) =>
        button.addEventListener('click', () => {
            setPressed(button, button.getAttribute('aria-pressed') !== 'true');
            saver.save(chosenThemes());
        }),
    );

    const saveNotes = () => {
        clearTimeout(notesTimer);
        notesTimer = null;
        saver.save({ notes: notes.value });
    };
    notes.addEventListener('input', () => {
        clearTimeout(notesTimer);
        notesTimer = setTimeout(saveNotes, NOTES_DELAY_MS);
    });
    notes.addEventListener('blur', () => notesTimer && saveNotes());

    window.addEventListener('beforeunload', (event) => {
        if (notesTimer || saver.busy) {
            event.preventDefault();
        }
    });
}

export function initReviewPanels() {
    document.querySelectorAll('[data-review-panel]').forEach(initPanel);
}
