<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= $config['HOST'] ?></loc>

        <changefreq>always</changefreq>

        <priority>0.5</priority>
    </url>

    <url>
        <loc><?= $config['HOST'] ?>/catalog</loc>

        <changefreq>weekly</changefreq>

        <priority>0.5</priority>
    </url>

    <url>
        <loc><?= $config['HOST'] ?>/signup</loc>

        <priority>0</priority>
    </url>

    <url>
        <loc><?= $config['HOST'] ?>/signin</loc>

        <priority>0</priority>
    </url>

    <url>
        <loc><?= $config['HOST'] ?>/forgot-password</loc>

        <priority>0</priority>
    </url>

    <?php foreach($pages as $page) echo "
        <url>
            <loc>{$config['HOST']}/pages/{$page['name']}</loc>

            <changefreq>always</changefreq>

            <priority>0.5</priority>
        </url>
    ";?>

    <?php foreach($movies as $movie) echo "
        <url>
            <loc>{$config['HOST']}/movie/{$movie->getId()}</loc>

            <lastmod>{$movie->getUpdatedAt()}</lastmod>

            <priority>1</priority>
        </url>
    ";?>

    <?php foreach($episodes as $episode) echo "
        <url>
            <loc>{$config['HOST']}/serie/{$episode->getSerieId()}/season/{$episode->getSeason()}/episode/{$episode->getEpisode()}</loc>

            <lastmod>{$episode->getUpdatedAt()}</lastmod>

            <priority>1</priority>
        </url>
    ";?>
</urlset> 