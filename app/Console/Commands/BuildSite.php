<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SiteScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BuildSite extends Command
{
    protected $signature = 'rmc:build-site {url} {theme} {--publish} {--name=}';
    protected $description = 'Build a microsite from a URL: scrape the home page, crawl & mirror internal pages, optionally publish.';

    public function handle(SiteScraper $sc): int
    {
        $url   = $this->argument('url');
        $theme = $this->argument('theme');
        if (!array_key_exists($theme, config('rmc.site_themes'))) {
            $this->error('Unknown theme. Options: ' . implode(', ', array_keys(config('rmc.site_themes'))));
            return self::FAILURE;
        }

        $this->info("Scraping {$url} …");
        $h = $sc->scrape($url);
        if (!$h['ok']) {
            $this->error('Scrape failed: ' . ($h['error'] ?: 'no content'));
            return self::FAILURE;
        }

        $site = Site::create([
            'theme'       => $theme,
            'source_url'  => $url,
            'name'        => $this->option('name') ?: ($h['name'] ?: parse_url($url, PHP_URL_HOST)),
            'tagline'     => $h['tagline'],
            'description' => $h['description'],
            'address'     => $h['address'],
            'city'        => $h['city'],
            'region'      => $h['region'],
            'country'     => $h['country'],
            'lat'         => $h['lat'],
            'lng'         => $h['lng'],
            'phone'       => $h['phone'],
            'email'       => $h['email'],
            'booking_url' => $h['booking_url'],
            'price_from'  => $h['price_from'],
            'hero_image'  => $h['hero_image'],
            'images'      => $h['images'],
        ]);

        $order = 0;
        foreach (array_slice($h['menu'], 0, 6) as $m) {
            $this->line("  mirroring: {$m['label']}");
            $p = $sc->scrapePage($m['url']);
            $title = $m['label'] ?: ($p['title'] ?: 'Page');
            $slug = Str::slug($title) ?: 'page';
            if ($site->pages()->where('slug', $slug)->exists()) $slug .= '-' . $order;
            $site->pages()->create([
                'title'      => Str::limit($title, 100, ''),
                'slug'       => $slug,
                'source_url' => $m['url'],
                'nav_order'  => $order++,
                'body'       => $p['body'],
                'images'     => $p['images'],
                'visible'    => true,
            ]);
        }

        if ($this->option('publish')) {
            $site->slug = Site::uniqueSlug($site->name);
            $site->published = true;
            $site->published_at = now();
            $site->save();
        }

        $this->newLine();
        $this->info('Built: ' . $site->name);
        $this->line('  preview : /motel/' . $site->preview_token . '   (password ' . $site->preview_password . ')');
        if ($site->slug) $this->line('  public  : /motel/' . $site->slug);
        $this->line('  pages   : ' . $site->pages()->count() . ' · images ' . count($site->images ?? []));

        return self::SUCCESS;
    }
}
