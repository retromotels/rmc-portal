<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\User;
use App\Services\SiteScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    private array $themes;

    public function __construct()
    {
        $this->themes = array_keys(config('rmc.site_themes'));
    }

    public function index()
    {
        return view('admin.sites.index', [
            'sites' => Site::withCount(['views as preview_hits' => fn ($q) => $q->where('kind', 'preview')->where('unlocked', true)])
                ->orderByDesc('created_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.sites.create', [
            'motels' => User::where('role', 'owner')->orderBy('motel')->get(),
        ]);
    }

    public function store(Request $r, SiteScraper $scraper)
    {
        $data = $r->validate([
            'theme'      => 'required|in:' . implode(',', $this->themes),
            'source_url' => 'required|url',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        $pulled = $scraper->scrape($data['source_url']);

        $site = Site::create([
            'user_id'     => $data['user_id'] ?? null,
            'theme'       => $data['theme'],
            'source_url'  => $data['source_url'],
            'name'        => $pulled['name'] ?: 'New microsite',
            'tagline'     => $pulled['tagline'],
            'description' => $pulled['description'],
            'address'     => $pulled['address'],
            'city'        => $pulled['city'],
            'region'      => $pulled['region'],
            'country'     => $pulled['country'],
            'lat'         => $pulled['lat'],
            'lng'         => $pulled['lng'],
            'phone'       => $pulled['phone'],
            'email'       => $pulled['email'],
            'booking_url' => $pulled['booking_url'],
            'price_from'  => $pulled['price_from'],
            'hero_image'  => $pulled['hero_image'],
            'images'      => $pulled['images'],
            'amenities'   => $pulled['amenities'],
        ]);

        $pages = $pulled['ok'] ? $this->crawlPages($site, $pulled['menu'], $scraper) : 0;

        $msg = $pulled['ok']
            ? 'Pulled ' . count($pulled['images']) . ' images and mirrored ' . $pages . ' internal ' . Str::plural('page', $pages) . '. Review and edit below.'
            : 'Site created, but the URL could not be fully read (' . ($pulled['error'] ?: 'no content') . '). Fill the details in manually.';

        return redirect()->route('admin.sites.edit', $site)->with('status', $msg);
    }

    public function edit(Site $site)
    {
        $site->load(['user', 'pages', 'views' => fn ($q) => $q->orderByDesc('created_at')->limit(50)]);
        return view('admin.sites.edit', ['site' => $site]);
    }

    public function recrawlPages(Site $site, SiteScraper $scraper)
    {
        $home = $scraper->scrape($site->source_url);
        if (!$home['ok']) {
            return back()->with('status', 'Could not re-read the site menu: ' . ($home['error'] ?: 'no content'));
        }
        $start = ((int) $site->pages()->max('nav_order')) + 1;
        $added = $this->crawlPages($site, $home['menu'], $scraper, $start);
        return back()->with('status', "Menu re-crawled — added {$added} new " . Str::plural('page', $added) . '.');
    }

    public function editPage(Site $site, SitePage $page)
    {
        abort_unless($page->site_id === $site->id, 404);
        return view('admin.sites.page', ['site' => $site, 'page' => $page]);
    }

    public function updatePage(Request $r, Site $site, SitePage $page)
    {
        abort_unless($page->site_id === $site->id, 404);
        $d = $r->validate([
            'title'       => 'required|string|max:120',
            'nav_order'   => 'nullable|integer',
            'body'        => 'nullable|string',
            'images_text' => 'nullable|string',
            'visible'     => 'nullable|boolean',
        ]);
        $page->update([
            'title'     => $d['title'],
            'nav_order' => $d['nav_order'] ?? 0,
            'body'      => $d['body'] ?? null,
            'images'    => $this->lines($d['images_text'] ?? ''),
            'visible'   => (bool) ($d['visible'] ?? false),
        ]);
        return redirect()->route('admin.sites.edit', $site)->with('status', 'Page “' . $page->title . '” saved.');
    }

    public function destroyPage(Site $site, SitePage $page)
    {
        abort_unless($page->site_id === $site->id, 404);
        $page->delete();
        return back()->with('status', 'Page removed.');
    }

    /* -------- crawling -------- */

    private function crawlPages(Site $site, array $menu, SiteScraper $scraper, int $startOrder = 0): int
    {
        $added = 0;
        $order = $startOrder;
        foreach (array_slice($menu, 0, 6) as $item) {
            if ($site->pages()->where('source_url', $item['url'])->exists()) continue;
            $p = $scraper->scrapePage($item['url']);
            $title = $item['label'] ?: ($p['title'] ?: 'Page');
            $site->pages()->create([
                'title'      => Str::limit($title, 100, ''),
                'slug'       => $this->pageSlug($site, $title),
                'source_url' => $item['url'],
                'nav_order'  => $order++,
                'body'       => $p['body'],
                'images'     => $p['images'],
                'visible'    => true,
            ]);
            $added++;
        }
        return $added;
    }

    private function pageSlug(Site $site, string $title): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $i = 2;
        while ($site->pages()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function update(Request $r, Site $site)
    {
        $data = $r->validate([
            'theme'       => 'required|in:' . implode(',', $this->themes),
            'name'        => 'required|string|max:160',
            'tagline'     => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'address'     => 'nullable|string|max:200',
            'city'        => 'nullable|string|max:120',
            'region'      => 'nullable|string|max:120',
            'country'     => 'nullable|string|max:120',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'phone'       => 'nullable|string|max:60',
            'email'       => 'nullable|string|max:160',
            'booking_url' => 'nullable|url',
            'price_from'  => 'nullable|string|max:60',
            'hero_image'  => 'nullable|string|max:1000',
            'images_text' => 'nullable|string',
            'amenities_text' => 'nullable|string',
            'published'   => 'nullable|boolean',
        ]);

        $site->fill([
            'theme' => $data['theme'],
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'country' => $data['country'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'booking_url' => $data['booking_url'] ?? null,
            'price_from' => $data['price_from'] ?? null,
            'hero_image' => $data['hero_image'] ?? null,
            'images' => $this->lines($data['images_text'] ?? ''),
            'amenities' => $this->lines($data['amenities_text'] ?? ''),
        ]);

        $wantPublished = (bool) ($data['published'] ?? false);
        $this->applyPublish($site, $wantPublished);
        $site->save();

        return redirect()->route('admin.sites.edit', $site)->with('status', 'Saved.');
    }

    public function togglePublish(Site $site)
    {
        $this->applyPublish($site, !$site->published);
        $site->save();

        return back()->with('status', $site->published
            ? 'Public page is now LIVE at ' . $site->publicUrl()
            : 'Public page turned OFF.');
    }

    public function rescrape(Site $site, SiteScraper $scraper)
    {
        $p = $scraper->scrape($site->source_url);
        if (!$p['ok']) {
            return back()->with('status', 'Re-pull failed: ' . ($p['error'] ?: 'no content'));
        }
        // Only fill blanks; append any new images.
        foreach (['name', 'tagline', 'description', 'address', 'city', 'region', 'country', 'lat', 'lng', 'phone', 'email', 'price_from', 'hero_image'] as $f) {
            if (blank($site->$f) && !blank($p[$f])) $site->$f = $p[$f];
        }
        $merged = collect($site->images ?? [])->merge($p['images'])->unique()->values()->all();
        $site->images = $merged;
        $site->save();

        return back()->with('status', 'Re-pulled from the URL — new content added to any blank fields.');
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('admin.sites.index')->with('status', 'Microsite deleted.');
    }

    /* -------- helpers -------- */

    private function applyPublish(Site $site, bool $publish): void
    {
        if ($publish) {
            if (blank($site->slug)) {
                $site->slug = Site::uniqueSlug($site->name, $site->id);
            }
            $site->published = true;
            $site->published_at = $site->published_at ?: Carbon::now();
        } else {
            $site->published = false;
        }
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($l) => trim($l))->filter()->unique()->values()->all();
    }
}
