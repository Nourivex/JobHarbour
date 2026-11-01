/**
 * Perilaku komponen bersama: menu tarik turun, dialog, dan status memuat pada tombol.
 */

/** Menu tarik turun berbasis [data-menu]. Satu pendengar di dokumen, aman untuk konten yang dirender ulang. */
export function initMenus() {
    const closeAll = (except) => {
        document.querySelectorAll('.ws-menu.is-open').forEach((m) => {
            if (m === except) return;
            m.classList.remove('is-open');
            m.querySelector('[data-menu-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-menu-trigger]');
        if (trigger) {
            const menu = trigger.closest('.ws-menu');
            const open = !menu.classList.contains('is-open');
            closeAll(menu);
            menu.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', String(open));
            if (open) {
                // Buka ke atas bila ruang di bawah tidak cukup
                const list = menu.querySelector('.ws-menu-list');
                list.classList.remove('up');
                const rect = list.getBoundingClientRect();
                if (rect.bottom > window.innerHeight - 8) list.classList.add('up');
                list.querySelector('.ws-menu-item:not([aria-disabled="true"])')?.focus();
            }
            return;
        }
        if (e.target.closest('.ws-menu-item')) closeAll();
        else if (!e.target.closest('.ws-menu')) closeAll();
    });

    document.addEventListener('keydown', (e) => {
        const menu = document.querySelector('.ws-menu.is-open');
        if (!menu) return;
        const items = [...menu.querySelectorAll('.ws-menu-item:not([aria-disabled="true"])')];
        const i = items.indexOf(document.activeElement);
        if (e.key === 'Escape') {
            closeAll();
            menu.querySelector('[data-menu-trigger]')?.focus();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            items[(i + 1) % items.length]?.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            items[(i - 1 + items.length) % items.length]?.focus();
        }
    });
}

export function menu(triggerLabel, items, { icon = '', triggerClass = 'ws-icon-btn' } = {}) {
    return `
        <div class="ws-menu">
            <button type="button" class="${triggerClass}" data-menu-trigger aria-haspopup="menu" aria-expanded="false" aria-label="${triggerLabel}">${icon}</button>
            <div class="ws-menu-list" role="menu">
                ${items.map((it) => (it === '-' ? '<div class="ws-menu-sep" role="separator"></div>' : `
                    <button type="button" role="menuitem" class="ws-menu-item ${it.danger ? 'danger' : ''}" ${it.disabled ? 'aria-disabled="true"' : ''} ${it.tip ? `title="${it.tip}"` : ''} ${it.attrs || ''}>
                        ${it.icon || ''}<span>${it.label}</span>
                    </button>`)).join('')}
            </div>
        </div>`;
}

/** <dialog> dengan penutupan lewat tombol [data-dialog-close] dan klik latar. */
export function bindDialog(dialog) {
    if (!dialog || dialog.dataset.bound) return dialog;
    dialog.dataset.bound = '1';
    dialog.addEventListener('click', (e) => {
        if (e.target.closest('[data-dialog-close]')) dialog.close();
        else if (e.target === dialog) {
            // Klik di area latar (di luar kotak dialog)
            const r = dialog.getBoundingClientRect();
            const inside = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
            if (!inside) dialog.close();
        }
    });
    return dialog;
}

export function openDialog(dialog) {
    bindDialog(dialog);
    if (!dialog.open) dialog.showModal();
    document.body.classList.add('overflow-hidden');
    dialog.addEventListener('close', () => document.body.classList.remove('overflow-hidden'), { once: true });
}

export function setBusy(btn, busy, label) {
    if (!btn) return;
    if (busy) {
        btn.dataset.label = btn.innerHTML;
        btn.classList.add('is-loading');
        btn.disabled = true;
        if (label) btn.textContent = label;
    } else {
        btn.classList.remove('is-loading');
        btn.disabled = false;
        if (btn.dataset.label) btn.innerHTML = btn.dataset.label;
    }
}

/** Debounce sederhana untuk kolom pencarian. */
export function debounce(fn, ms = 200) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
    };
}
