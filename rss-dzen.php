<?php
/**
 * Custom RSS feed for Dzen (Яндекс Дзен)
 * URL: /feed/dzen/
 */

defined( 'ABSPATH' ) || exit;

header( 'Content-Type: application/rss+xml; charset=UTF-8' );

$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => [
        [
            'taxonomy' => 'post_format',
            'field'    => 'slug',
            'terms'    => [ 'post-format-video' ],
            'operator' => 'NOT IN',
        ],
    ],
];
$query = new WP_Query( $args );

$site_name = get_bloginfo( 'name' );
$site_desc = get_bloginfo( 'description' );
$site_url  = get_bloginfo( 'url' );
$feed_url  = $site_url . '/feed/dzen/';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
    <title><?= esc_html( $site_name ) ?></title>
    <link><?= esc_url( $site_url ) ?></link>
    <description><?= esc_html( $site_desc ) ?></description>
    <language>ru</language>
    <atom:link href="<?= esc_url( $feed_url ) ?>" rel="self" type="application/rss+xml"/>
    <lastBuildDate><?= date( 'D, d M Y H:i:s O' ) ?></lastBuildDate>

<?php while ( $query->have_posts() ) : $query->the_post();
    $post_id   = get_the_ID();
    $title     = get_post_meta( $post_id, '_hubr_seo_title', true ) ?: get_the_title();
    $desc      = get_post_meta( $post_id, '_hubr_seo_description', true ) ?: get_the_excerpt();
    $content   = get_the_content();
    $permalink = get_permalink();
    $pub_date  = date( 'D, d M Y H:i:s O', get_post_timestamp() );
    $author    = get_the_author();

    // Featured image — full size, skip if < 700px (Dzen/VK minimum)
    $image_url  = '';
    $image_w    = 0;
    $image_h    = 0;
    $image_size = 0;
    $image_mime = 'image/jpeg';
    if ( has_post_thumbnail() ) {
        $thumb_id = get_post_thumbnail_id();
        $thumb    = wp_get_attachment_image_src( $thumb_id, 'full' )
                 ?: wp_get_attachment_image_src( $thumb_id, 'large' );
        $w = (int) ( $thumb[1] ?? 0 );
        if ( $thumb && $w >= 700 ) {
            $image_url = $thumb[0];
            $image_w   = $w;
            $image_h   = (int) ( $thumb[2] ?? 0 );
            // Real file size — required by RSS 2.0 <enclosure>
            $file = get_attached_file( $thumb_id );
            if ( $file && file_exists( $file ) ) {
                $image_size = (int) filesize( $file );
            }
            // Real MIME type
            $mime = get_post_mime_type( $thumb_id );
            if ( $mime && str_starts_with( $mime, 'image/' ) ) {
                $image_mime = $mime;
            }
        }
    }

    // Full processed content (shortcodes, filters)
    $content = apply_filters( 'the_content', $content );

    // Make content absolute URLs
    $content = str_replace( 'href="/', 'href="' . $site_url . '/', $content );
    $content = str_replace( 'src="/', 'src="' . $site_url . '/', $content );

    // Prepend featured image to content if exists
    if ( $image_url ) {
        $content = '<figure><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '"/></figure>' . $content;
    }
?>
    <item>
        <title><?= esc_html( $title ) ?></title>
        <link><?= esc_url( $permalink ) ?></link>
        <guid isPermaLink="true"><?= esc_url( $permalink ) ?></guid>
        <pubDate><?= $pub_date ?></pubDate>
        <dc:creator><?= esc_html( $author ) ?></dc:creator>
        <description><?= esc_html( wp_trim_words( $desc, 60 ) ) ?></description>
        <content:encoded><![CDATA[<?= $content ?>]]></content:encoded>
<?php if ( $image_url ) : ?>
        <enclosure url="<?= esc_url( $image_url ) ?>" type="<?= esc_attr( $image_mime ) ?>" length="<?= $image_size ?>"/>
        <media:content url="<?= esc_url( $image_url ) ?>" medium="image" type="<?= esc_attr( $image_mime ) ?>"<?= $image_w ? ' width="' . $image_w . '" height="' . $image_h . '"' : '' ?>/>
        <media:thumbnail url="<?= esc_url( $image_url ) ?>"/>
<?php endif; ?>
    </item>
<?php endwhile; wp_reset_postdata(); ?>

</channel>
</rss>
