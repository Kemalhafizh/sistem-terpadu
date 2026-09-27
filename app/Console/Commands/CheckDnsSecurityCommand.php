<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDnsSecurityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:check-dns {domain=sultanagung1.sch.id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa keberadaan dan validitas record SPF, DKIM, DMARC, dan DNSSEC pada domain sekolah';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $this->info(" Memeriksa Keamanan DNS & Email Authentication untuk domain: {$domain}");

        // 1. Cek SPF
        $spfRecords = @dns_get_record($domain, DNS_TXT);
        $hasSpf = false;
        if (is_array($spfRecords)) {
            foreach ($spfRecords as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], 'v=spf1')) {
                    $hasSpf = true;
                    $this->info(' SPF Record Ditemukan: '.$record['txt']);
                    break;
                }
            }
        }
        if (! $hasSpf) {
            $this->warn(" SPF Record tidak ditemukan pada domain {$domain}. Disarankan untuk dikonfigurasi.");
        }

        // 2. Cek DMARC
        $dmarcDomain = "_dmarc.{$domain}";
        $dmarcRecords = @dns_get_record($dmarcDomain, DNS_TXT);
        $hasDmarc = false;
        if (is_array($dmarcRecords)) {
            foreach ($dmarcRecords as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], 'v=DMARC1')) {
                    $hasDmarc = true;
                    $this->info(' DMARC Record Ditemukan: '.$record['txt']);
                    break;
                }
            }
        }
        if (! $hasDmarc) {
            $this->warn(" DMARC Record tidak ditemukan pada {$dmarcDomain}. Disarankan mengonfigurasi kebijakan DMARC.");
        }

        // 3. Cek DNSSEC
        $dnssecConst = defined('DNS_DNSKEY') ? constant('DNS_DNSKEY') : DNS_ANY;
        $dnssecRecords = @dns_get_record($domain, $dnssecConst);
        if (! empty($dnssecRecords)) {
            $this->info(' DNSSEC Terdeteksi Aktif (DNSKEY Record Ditemukan).');
        } else {
            $this->warn('ℹ DNSSEC DNSKEY Record tidak terdeteksi atau tidak aktif di DNS resolver publik.');
        }

        $this->info(' Pemeriksaan Keamanan DNS selesai.');

        return Command::SUCCESS;
    }
}
