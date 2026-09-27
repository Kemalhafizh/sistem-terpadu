<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DeveloperPortalController extends Controller
{
    /**
     * Render the Developer Portal Dashboard & Guides
     */
    public function index(Request $request, $category = null, $page = null)
    {
        $docsDir = base_path('docs');

        // Default to README
        $selectedFile = 'README.md';
        $title = 'Introduction';

        if ($category && ! $page) {
            // Root doc file (e.g. /developer/ARCHITECTURE)
            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '', $category);
            $selectedFile = $safeCategory.'.md';
            $title = ucwords(str_replace('_', ' ', $safeCategory));
        } elseif ($category && $page) {
            // Subdirectory doc file (e.g. /developer/adr/001-service-pattern)
            $safeCategory = preg_replace('/[^a-zA-Z0-9_-]/', '', $category);
            $safePage = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);
            $selectedFile = $safeCategory.'/'.$safePage.'.md';
            $title = ucwords(str_replace('-', ' ', $safePage));
        }

        $fullPath = $docsDir.'/'.$selectedFile;

        if (! File::exists($fullPath)) {
            abort(404, 'Dokumentasi tidak ditemukan.');
        }

        $markdown = File::get($fullPath);
        $htmlContent = $this->parseMarkdown($markdown);

        // Sidebar Navigation links grouping
        $navigation = [
            'Arsitektur & Sistem' => [
                ['label' => 'Overview & Portal Hub', 'route' => route('developer.portal')],
                ['label' => 'Arsitektur Sistem & Desain', 'route' => route('developer.page', ['category' => 'ARCHITECTURE'])],
                ['label' => 'Keamanan & SIEM SIgnals', 'route' => route('developer.page', ['category' => 'SECURITY'])],
            ],
            'Panduan Developer' => [
                ['label' => 'Panduan Instalasi Lokal', 'route' => route('developer.page', ['category' => 'INSTALLATION'])],
                ['label' => 'Pengujian (Pest PHP Testing)', 'route' => route('developer.page', ['category' => 'TESTING'])],
                ['label' => 'Panduan Deployment', 'route' => route('developer.page', ['category' => 'DEPLOYMENT'])],
                ['label' => 'Kontribusi & Git Workflow', 'route' => route('developer.page', ['category' => 'CONTRIBUTING'])],
                ['label' => 'REST API v1 Reference', 'route' => route('developer.page', ['category' => 'API'])],
                ['label' => 'Riwayat Perubahan (Changelog)', 'route' => route('developer.page', ['category' => 'CHANGELOG'])],
            ],
            'Architecture Decisions (ADR)' => [
                ['label' => 'ADR 001: Service Pattern', 'route' => route('developer.subpage', ['category' => 'adr', 'page' => '001-service-pattern'])],
                ['label' => 'ADR 002: Laravel Reverb', 'route' => route('developer.subpage', ['category' => 'adr', 'page' => '002-websocket-reverb'])],
            ],
            'Dokumentasi Modul Bisnis' => [
                ['label' => 'Modul 1: Akademik & Rapor', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'academic'])],
                ['label' => 'Modul 2: Keuangan & SPP', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'finance'])],
                ['label' => 'Modul 3: HRD & Absen GPS', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'hrd'])],
                ['label' => 'Modul 4: E-Learning & CBT', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'elearning'])],
                ['label' => 'Modul 5: Komunikasi & WA Gateway', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'communication'])],
                ['label' => 'Modul 6: Hub Impor & Ekspor', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'import_export'])],
                ['label' => 'Modul 7: Workflow Engine', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'workflow'])],
                ['label' => 'Modul 8: E-Portofolio Siswa', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'portfolio'])],
                ['label' => 'Modul 9: SIEM & Keamanan Sesi', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'security'])],
                ['label' => 'Modul 10: Custom Report Builder', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'report_builder'])],
                ['label' => 'Modul Tambahan: AI Chatbot (Lokal)', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'chatbot'])],
                ['label' => 'Modul Tambahan: Gamifikasi Sekolah', 'route' => route('developer.subpage', ['category' => 'modules', 'page' => 'gamification'])],
            ],
        ];

        return view('docs.index', compact('htmlContent', 'navigation', 'title', 'selectedFile'));
    }

    /**
     * Lightweight custom Markdown to HTML Parser tailored for Tailwind
     */
    private function parseMarkdown($markdown)
    {
        // Protect code blocks first to prevent styling corruption
        $codeBlocks = [];
        $markdown = preg_replace_callback('/```(\w*)\n([\s\S]*?)\n```/', function ($matches) use (&$codeBlocks) {
            $lang = $matches[1] ?: 'code';
            $code = htmlspecialchars($matches[2]);
            $placeholder = '<!--CODE_BLOCK_'.count($codeBlocks).'-->';
            $codeBlocks[] = "<pre class=\"bg-gray-950 p-5 rounded-2xl border border-white/5 font-mono text-sm text-gray-300 overflow-x-auto my-6 shadow-inner\"><code class=\"language-{$lang}\">{$code}</code></pre>";

            return $placeholder;
        }, $markdown);

        // Protect inline code blocks
        $inlineCode = [];
        $markdown = preg_replace_callback('/`([^`]+)`/', function ($matches) use (&$inlineCode) {
            $code = htmlspecialchars($matches[1]);
            $placeholder = '<!--INLINE_CODE_'.count($inlineCode).'-->';
            $inlineCode[] = "<code class=\"bg-white/5 px-2 py-0.5 rounded-lg font-mono text-pink-400 text-sm border border-white/5\">{$code}</code>";

            return $placeholder;
        }, $markdown);

        // Convert GitHub Alerts
        // > [!NOTE]
        $markdown = preg_replace_callback('/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\n>\s*([\s\S]*?)(?=\n\n|\n[^\s>])/m', function ($matches) {
            $type = strtoupper($matches[1]);
            $text = trim(preg_replace('/^>\s?/m', '', $matches[2]));

            $bg = 'bg-blue-500/10 border-blue-500/30 text-blue-300';
            $icon = 'ℹ';
            if ($type === 'TIP') {
                $bg = 'bg-green-500/10 border-green-500/30 text-green-300';
                $icon = '';
            } elseif ($type === 'IMPORTANT' || $type === 'WARNING') {
                $bg = 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300';
                $icon = '';
            } elseif ($type === 'CAUTION') {
                $bg = 'bg-red-500/10 border-red-500/30 text-red-300';
                $icon = '';
            }

            return "<div class=\"p-4 my-6 rounded-2xl border {$bg} flex gap-3 items-start\"><span class=\"text-xl\">{$icon}</span><div><strong class=\"block text-sm uppercase tracking-wider mb-1\">{$type}</strong><span class=\"text-sm leading-relaxed\">{$text}</span></div></div>";
        }, $markdown);

        // Convert standard Blockquotes
        $markdown = preg_replace('/^>\s*(.+)$/m', '<blockquote class="border-l-4 border-purple-500 pl-4 my-4 italic text-gray-400 leading-relaxed">$1</blockquote>', $markdown);

        // Convert Headers
        $markdown = preg_replace('/^#\s*(.+)$/m', '<h1 class="font-outfit font-black text-4xl text-white mt-10 mb-6 border-b border-white/5 pb-3 bg-gradient-to-r from-white via-gray-100 to-gray-400 bg-clip-text text-transparent">$1</h1>', $markdown);
        $markdown = preg_replace('/^##\s*(.+)$/m', '<h2 class="font-outfit font-extrabold text-2xl text-blue-400 mt-12 mb-4 flex items-center gap-2 border-b border-white/5 pb-2">$1</h2>', $markdown);
        $markdown = preg_replace('/^###\s*(.+)$/m', '<h3 class="font-outfit font-bold text-xl text-purple-400 mt-8 mb-3">$1</h3>', $markdown);

        // Convert Bold
        $markdown = preg_replace('/\*\*([^*]+)\*\*/', '<strong class="font-bold text-white">$1</strong>', $markdown);

        // Convert Links
        $markdown = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" class="text-blue-400 hover:text-blue-300 font-semibold underline transition">$1</a>', $markdown);

        // Convert Tables
        // Capture standard Markdown tables and convert them to Tailwind CSS tables
        $markdown = preg_replace_callback('/(?:\|.*\|(?:\n|$))+/', function ($matches) {
            $rows = explode("\n", trim($matches[0]));
            $htmlTable = '<div class="overflow-x-auto my-6 rounded-2xl border border-white/5 shadow-xl"><table class="w-full text-left border-collapse text-sm">';
            $isHeader = true;
            foreach ($rows as $row) {
                // Ignore the separator line like |---|---|
                if (preg_match('/^\|\s*:?-+:?\s*\|/', $row)) {
                    continue;
                }

                $cols = array_filter(explode('|', $row), function ($val) {
                    return $val !== '';
                });
                $cols = array_map('trim', $cols);

                if (empty($cols)) {
                    continue;
                }

                if ($isHeader) {
                    $htmlTable .= '<thead class="bg-white/5 font-outfit text-white border-b border-white/5"><tr>';
                    foreach ($cols as $col) {
                        $htmlTable .= "<th class=\"px-6 py-4 font-semibold\">{$col}</th>";
                    }
                    $htmlTable .= '</tr></thead><tbody class="divide-y divide-white/5">';
                    $isHeader = false;
                } else {
                    $htmlTable .= '<tr class="hover:bg-white/5 transition-all"> ';
                    foreach ($cols as $col) {
                        $htmlTable .= "<td class=\"px-6 py-4 text-gray-300\">{$col}</td>";
                    }
                    $htmlTable .= '</tr>';
                }
            }
            $htmlTable .= '</tbody></table></div>';

            return $htmlTable;
        }, $markdown);

        // Convert Lists (Bullet and Numeric)
        $markdown = preg_replace('/^\s*[-*]\s+(.+)$/m', '<li class="ml-6 list-disc text-gray-300 my-1.5 leading-relaxed">$1</li>', $markdown);
        $markdown = preg_replace('/^\s*\d+\.\s+(.+)$/m', '<li class="ml-6 list-decimal text-gray-300 my-1.5 leading-relaxed">$1</li>', $markdown);

        // Group list items
        $markdown = preg_replace('/(<li>.*<\/li>)/s', '<ul class="my-4 space-y-1">$1</ul>', $markdown);

        // Restore protected elements
        foreach ($codeBlocks as $index => $html) {
            $markdown = str_replace('<!--CODE_BLOCK_'.$index.'-->', $html, $markdown);
        }
        foreach ($inlineCode as $index => $html) {
            $markdown = str_replace('<!--INLINE_CODE_'.$index.'-->', $html, $markdown);
        }

        // Separate paragraphs by double linebreaks, except inside tags
        $lines = explode("\n\n", $markdown);
        foreach ($lines as &$line) {
            $line = trim($line);
            if ($line && ! str_starts_with($line, '<h') && ! str_starts_with($line, '<div') && ! str_starts_with($line, '<pre') && ! str_starts_with($line, '<ul') && ! str_starts_with($line, '<table') && ! str_starts_with($line, '<block')) {
                $line = "<p class=\"text-gray-400 leading-relaxed my-4\">{$line}</p>";
            }
        }
        $markdown = implode("\n", $lines);

        return $markdown;
    }
}
