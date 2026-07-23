@props(['targetId' => 'question_text'])

<div x-data="{
    latexPreview: '',
    updatePreview() {
        const el = document.getElementById('{{ $targetId }}');
        this.latexPreview = el ? el.value : '';
        this.$nextTick(() => {
            if (typeof window.renderSistaLatex === 'function') {
                const previewEl = document.getElementById('latex-preview-box-{{ $targetId }}');
                if (previewEl) window.renderSistaLatex(previewEl);
            }
        });
    },
    insertLatex(snippet) {
        const el = document.getElementById('{{ $targetId }}');
        if (!el) return;
        const start = el.selectionStart || 0;
        const end = el.selectionEnd || 0;
        const text = el.value;
        el.value = text.substring(0, start) + snippet + text.substring(end);
        el.selectionStart = el.selectionEnd = start + snippet.length;
        el.focus();
        this.updatePreview();
    }
}" x-init="
    const targetEl = document.getElementById('{{ $targetId }}');
    if (targetEl) {
        targetEl.addEventListener('input', () => updatePreview());
        updatePreview();
    }
" class="space-y-2 mt-2">
    <!-- Quick LaTeX Toolbar Bar -->
    <div class="p-2.5 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700/80 flex flex-wrap items-center gap-1.5 text-xs">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            LaTeX Saintek:
        </span>

        <button type="button" @click="insertLatex('\\frac{a}{b}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Pecahan">
            \frac{a}{b}
        </button>

        <button type="button" @click="insertLatex('\\sqrt{x}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Akar">
            \sqrt{x}
        </button>

        <button type="button" @click="insertLatex('x^{2}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Pangkat">
            x^{2}
        </button>

        <button type="button" @click="insertLatex('\\int_{a}^{b}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Integral">
            \int_{a}^{b}
        </button>

        <button type="button" @click="insertLatex('\\sum_{i=1}^{n}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Sigma">
            \sum_{i=1}^{n}
        </button>

        <button type="button" @click="insertLatex('\\vec{F}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs" title="Sisipkan Vektor">
            \vec{F}
        </button>

        <button type="button" @click="insertLatex('\\ce{H2SO4}')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-emerald-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 transition-all shadow-xs" title="Sisipkan Reaksi Kimia (mhchem)">
            \ce{Kimia}
        </button>

        <button type="button" @click="insertLatex('\\alpha')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs">
            \alpha
        </button>

        <button type="button" @click="insertLatex('\\beta')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs">
            \beta
        </button>

        <button type="button" @click="insertLatex('\\pi')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs">
            \pi
        </button>

        <button type="button" @click="insertLatex('\\theta')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs">
            \theta
        </button>

        <button type="button" @click="insertLatex('\\infty')" class="px-2 py-1 bg-white dark:bg-slate-900 hover:bg-indigo-50 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 text-indigo-600 dark:text-indigo-400 transition-all shadow-xs">
            \infty
        </button>
    </div>

    <!-- Live Rendered LaTeX Math Preview Box -->
    <div x-show="latexPreview.length > 0" class="p-3 bg-white dark:bg-slate-950 rounded-xl border border-indigo-200 dark:border-indigo-900/50 shadow-xs">
        <div class="flex justify-between items-center mb-1.5 border-b border-gray-100 dark:border-gray-800 pb-1">
            <span class="text-[10px] font-black uppercase text-indigo-500 tracking-wider">Pratinjau Formula Matematika / Saintek (Live Preview):</span>
            <span class="text-[9px] font-mono text-gray-400">Gunakan $...$ (Inline) atau $$...$$ (Display Block)</span>
        </div>
        <div id="latex-preview-box-{{ $targetId }}" class="text-sm font-semibold text-gray-800 dark:text-gray-100 min-h-[1.5rem]" x-html="latexPreview">
        </div>
    </div>
</div>
