import './bootstrap';

document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-menu]')?.classList.toggle('open');
});

const app = document.querySelector('[data-compare-app]');
if (app) {
    const form = document.querySelector('#compareForm');
    const leftInput = document.querySelector('#leftInput');
    const rightInput = document.querySelector('#rightInput');
    const leftNumbers = document.querySelector('[data-lines="left"]');
    const rightNumbers = document.querySelector('[data-lines="right"]');
    const panel = document.querySelector('#resultsPanel');
    const leftOutput = document.querySelector('[data-left-diff]');
    const rightOutput = document.querySelector('[data-right-diff]');
    const unifiedOutput = document.querySelector('[data-unified-output]');
    const sideOutput = document.querySelector('[data-side-output]');
    const viewSelect = document.querySelector('#diffView');
    let patchText = '';

    const escapeHtml = (text) => text.replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    }[char]));

    const lineCount = (value) => Math.max(1, value.split('\n').length);
    const updateNumbers = (side) => {
        const input = side === 'left' ? leftInput : rightInput;
        const target = side === 'left' ? leftNumbers : rightNumbers;
        target.textContent = Array.from({ length: lineCount(input.value) }, (_, i) => i + 1).join('\n');
        target.scrollTop = input.scrollTop;
    };

    const normalize = (value) => {
        let result = value;
        if (document.querySelector('[name="ignore_whitespace"]')?.checked) result = result.replace(/\s+/g, '');
        if (document.querySelector('[name="ignore_case"]')?.checked) result = result.toLocaleLowerCase();
        return result;
    };

    const tokenize = (line) => line.match(/\s+|[^\s]+/g) || [''];
    const tokenMarkup = (line, counterpart, changedSide) => {
        const tokens = tokenize(line);
        const other = tokenize(counterpart || '');
        if (tokens.length > 250 || other.length > 250) return syntaxMarkup(line);
        const dp = Array.from({ length: tokens.length + 1 }, () => new Uint16Array(other.length + 1));
        for (let i = tokens.length - 1; i >= 0; i--) {
            for (let j = other.length - 1; j >= 0; j--) {
                dp[i][j] = tokens[i] === other[j] ? dp[i + 1][j + 1] + 1 : Math.max(dp[i + 1][j], dp[i][j + 1]);
            }
        }
        const unmatched = new Set();
        let i = 0, j = 0;
        while (i < tokens.length && j < other.length) {
            if (tokens[i] === other[j]) { i++; j++; }
            else if (dp[i + 1][j] >= dp[i][j + 1]) unmatched.add(i++);
            else j++;
        }
        while (i < tokens.length) unmatched.add(i++);
        return tokens.map((token, index) => unmatched.has(index)
            ? `<span class="${changedSide === 'left' ? 'word-remove' : 'word-add'}">${escapeHtml(token)}</span>`
            : syntaxMarkup(token)).join('');
    };

    const syntaxMarkup = (value) => {
        const pattern = /(\/\/.*|#.*|\/\*.*?\*\/|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|`(?:\\.|[^`\\])*`|\b(?:function|const|let|var|return|if|else|for|foreach|while|new|class|public|private|protected|echo|async|await|import|from|export|def|lambda|true|false|null|None|try|catch|throw|yield|static|use|namespace)\b|\b\d+(?:\.\d+)?\b)/g;
        let output = '', lastIndex = 0;
        for (const match of value.matchAll(pattern)) {
            output += escapeHtml(value.slice(lastIndex, match.index));
            const token = match[0];
            const type = /^(\/\/|#|\/\*)/.test(token) ? 'comment'
                : /^("|'|`)/.test(token) ? 'string'
                    : /^\d/.test(token) ? 'number' : 'keyword';
            output += `<span class="token-${type}">${escapeHtml(token)}</span>`;
            lastIndex = match.index + token.length;
        }
        return output + escapeHtml(value.slice(lastIndex));
    };

    const buildOps = (leftLines, rightLines) => {
        const n = leftLines.length, m = rightLines.length;
        const dp = Array.from({ length: n + 1 }, () => new Uint16Array(m + 1));
        for (let i = n - 1; i >= 0; i--) {
            for (let j = m - 1; j >= 0; j--) {
                dp[i][j] = normalize(leftLines[i]) === normalize(rightLines[j])
                    ? dp[i + 1][j + 1] + 1 : Math.max(dp[i + 1][j], dp[i][j + 1]);
            }
        }
        const ops = [];
        let i = 0, j = 0;
        while (i < n && j < m) {
            if (normalize(leftLines[i]) === normalize(rightLines[j])) ops.push({ type: 'same', left: leftLines[i], right: rightLines[j], leftNo: i + 1, rightNo: j + 1 }), i++, j++;
            else if (dp[i + 1][j] >= dp[i][j + 1]) ops.push({ type: 'remove', left: leftLines[i], leftNo: i + 1 }), i++;
            else ops.push({ type: 'add', right: rightLines[j], rightNo: j + 1 }), j++;
        }
        while (i < n) ops.push({ type: 'remove', left: leftLines[i], leftNo: i + 1 }), i++;
        while (j < m) ops.push({ type: 'add', right: rightLines[j], rightNo: j + 1 }), j++;

        const rows = [];
        for (let index = 0; index < ops.length;) {
            if (ops[index].type === 'same') { rows.push(ops[index++]); continue; }
            const block = [];
            while (index < ops.length && ops[index].type !== 'same') block.push(ops[index++]);
            const removals = block.filter((op) => op.type === 'remove');
            const additions = block.filter((op) => op.type === 'add');
            const paired = Math.min(removals.length, additions.length);
            for (let pair = 0; pair < paired; pair++) rows.push({ type: 'modify', left: removals[pair].left, right: additions[pair].right, leftNo: removals[pair].leftNo, rightNo: additions[pair].rightNo });
            removals.slice(paired).forEach((op) => rows.push(op));
            additions.slice(paired).forEach((op) => rows.push(op));
        }
        return { ops, rows };
    };

    const rowMarkup = (text, lineNo, type, side, counterpart = '') => {
        if (text === undefined) return `<div class="diff-row blank"><span class="diff-ln"></span><code class="diff-code"> </code></div>`;
        let markup = escapeHtml(text);
        if (type === 'modify') markup = tokenMarkup(text, counterpart, side);
        else if (type === 'same') markup = syntaxMarkup(text);
        const css = type === 'remove' ? 'remove' : type === 'add' ? 'add' : type === 'modify' ? 'modify' : 'same';
        return `<div class="diff-row ${css}"><span class="diff-ln">${lineNo ?? ''}</span><code class="diff-code">${markup || ' '}</code></div>`;
    };

    const renderDiff = () => {
        const leftLines = leftInput.value.replace(/\r\n?/g, '\n').split('\n');
        const rightLines = rightInput.value.replace(/\r\n?/g, '\n').split('\n');
        const { ops, rows } = buildOps(leftLines, rightLines);
        let additions = 0, deletions = 0, changes = 0;
        rows.forEach((row) => { if (row.type === 'add') additions++; else if (row.type === 'remove') deletions++; else if (row.type === 'modify') changes++; });
        const leftHtml = rows.map((row) => rowMarkup(row.left, row.leftNo, row.type, 'left', row.right)).join('');
        const rightHtml = rows.map((row) => rowMarkup(row.right, row.rightNo, row.type, 'right', row.left)).join('');
        leftOutput.innerHTML = leftHtml;
        rightOutput.innerHTML = rightHtml;

        const unified = [];
        ops.forEach((op) => {
            if (op.type === 'same') unified.push(`<div class="diff-row same"><span class="diff-prefix"> </span><span class="diff-ln">${op.leftNo}</span><code class="diff-code">${syntaxMarkup(op.left) || ' '}</code></div>`);
            if (op.type === 'remove') unified.push(`<div class="diff-row remove"><span class="diff-prefix">−</span><span class="diff-ln">${op.leftNo}</span><code class="diff-code">${escapeHtml(op.left) || ' '}</code></div>`);
            if (op.type === 'add') unified.push(`<div class="diff-row add"><span class="diff-prefix">+</span><span class="diff-ln">${op.rightNo}</span><code class="diff-code">${escapeHtml(op.right) || ' '}</code></div>`);
        });
        unifiedOutput.innerHTML = unified.join('');
        const changedLabels = [];
        if (changes) changedLabels.push(`<span class="change-pill mod">${changes} ${changes === 1 ? 'change' : 'changes'}</span>`);
        if (additions) changedLabels.push(`<span class="change-pill add">↑ ${additions} ${additions === 1 ? 'addition' : 'additions'}</span>`);
        if (deletions) changedLabels.push(`<span class="change-pill del">↓ ${deletions} ${deletions === 1 ? 'deletion' : 'deletions'}</span>`);
        document.querySelector('[data-change-counts]').innerHTML = changedLabels.join('') || '<span class="change-pill add">No changes</span>';
        document.querySelector('[data-result-summary]').textContent = changes + additions + deletions === 0
            ? 'These versions are identical.' : `${changes + additions + deletions} changed line${changes + additions + deletions === 1 ? '' : 's'} found.`;

        patchText = [
            '--- Original', '+++ Changed',
            `@@ -1,${leftLines.length} +1,${rightLines.length} @@`,
            ...ops.map((op) => `${op.type === 'same' ? ' ' : op.type === 'remove' ? '-' : '+'}${op.type === 'remove' ? op.left : op.type === 'add' ? op.right : op.left}`),
        ].join('\n');
        const unifiedSelected = viewSelect.value === 'unified';
        sideOutput.hidden = unifiedSelected;
        unifiedOutput.hidden = !unifiedSelected;
        panel.classList.add('is-visible');
    };

    [leftInput, rightInput].forEach((input, index) => {
        const side = index === 0 ? 'left' : 'right';
        input.addEventListener('input', () => updateNumbers(side));
        input.addEventListener('scroll', () => {
            (side === 'left' ? leftNumbers : rightNumbers).scrollTop = input.scrollTop;
            const other = side === 'left' ? rightInput : leftInput;
            if (!other.dataset.syncing) {
                other.dataset.syncing = '1';
                other.scrollTop = input.scrollTop;
                (side === 'left' ? rightNumbers : leftNumbers).scrollTop = other.scrollTop;
                requestAnimationFrame(() => delete other.dataset.syncing);
            }
        });
        updateNumbers(side);
    });

    viewSelect.addEventListener('change', () => {
        if (panel.classList.contains('is-visible')) renderDiff();
    });
    document.querySelector('[data-swap]').addEventListener('click', () => {
        const value = leftInput.value; leftInput.value = rightInput.value; rightInput.value = value;
        const leftLabel = document.querySelector('[data-file-label="left"]');
        const rightLabel = document.querySelector('[data-file-label="right"]');
        const labelValue = leftLabel.textContent; leftLabel.textContent = rightLabel.textContent; rightLabel.textContent = labelValue;
        updateNumbers('left'); updateNumbers('right');
        if (panel.classList.contains('is-visible')) renderDiff();
    });
    document.querySelector('[data-clear]').addEventListener('click', () => {
        leftInput.value = ''; rightInput.value = '';
        document.querySelectorAll('[data-file-label]').forEach((label) => label.textContent = 'Paste or type here');
        document.querySelectorAll('[data-upload]').forEach((input) => input.value = '');
        updateNumbers('left'); updateNumbers('right');
        panel.classList.remove('is-visible');
    });
    document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
        const value = button.dataset.copy === 'left' ? leftInput.value : rightInput.value;
        try { await navigator.clipboard.writeText(value); button.textContent = '✓'; setTimeout(() => button.textContent = '▢', 900); } catch (_) {}
    }));
    document.querySelectorAll('[data-upload]').forEach((input) => input.addEventListener('change', async () => {
        const file = input.files?.[0]; if (!file) return;
        if (file.size > 1024 * 1024) { alert('Choose a text file smaller than 1 MB.'); input.value = ''; return; }
        try {
            const bytes = await file.arrayBuffer();
            const content = new TextDecoder('utf-8', { fatal: true }).decode(bytes);
            const side = input.dataset.upload;
            const editor = side === 'left' ? leftInput : rightInput;
            editor.value = content;
            document.querySelector(`[data-file-label="${side}"]`).textContent = file.name;
            updateNumbers(side);
        } catch (_) { alert('This file could not be read as text.'); }
    }));
    document.querySelector('[data-copy-result]').addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(patchText); } catch (_) {}
    });
    document.querySelector('[data-download]').addEventListener('click', () => {
        const blob = new Blob([patchText], { type: 'text/x-diff;charset=utf-8' });
        const url = URL.createObjectURL(blob); const anchor = document.createElement('a');
        anchor.href = url; anchor.download = 'comparison.diff'; anchor.click(); URL.revokeObjectURL(url);
    });
    if (app.dataset.hasCompared === '1') renderDiff();
}
